<?php

namespace Tests\Feature;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Покриває graceful-419-recovery handler з bootstrap/app.php
 * ($exceptions->render(HttpExceptionInterface ... getStatusCode() === 419)).
 *
 * ── ЧОМУ ЦЕ НЕТРИВІАЛЬНО ──────────────────────────────────────────────────
 * Laravel у штатному режимі НЕ кидає TokenMismatchException під час тестів:
 * ValidateCsrfToken::handle() першим ділом перевіряє runningUnitTests()
 * (app->runningInConsole() && app->runningUnitTests()) і пропускає валідацію
 * токена. Тобто звичайний $this->post(..., ['_token' => 'bad']) у тесті
 * НІКОЛИ не дійде до 419 — CSRF просто вимкнено під phpunit.
 *
 * Щоб чесно (end-to-end) прогнати реальний ланцюг:
 *     TokenMismatchException
 *       → Laravel::prepareException() конвертує у HttpException(419)
 *       → render-callback з bootstrap/app.php
 * ми підміняємо CSRF-middleware на сабклас, у якого runningUnitTests() === false.
 * Решта поведінки middleware — справжня (hash_equals токена сесії проти _token).
 *
 * ВАЖЛИВО про ім'я класу: web-група у Laravel 12 містить
 * Illuminate\Foundation\Http\Middleware\ValidateCsrfToken (новий канон),
 * а НЕ застарілий VerifyCsrfToken. ValidateCsrfToken extends VerifyCsrfToken,
 * тож bind() треба вішати саме на ValidateCsrfToken — інакше pipeline
 * make()-ить оригінал і override не застосується (перевірено: handle() не
 * викликається, CSRF тихо проходить).
 *
 * Жодного withoutMiddleware('web'): група web лишається повною, інакше
 * StartSession не стартує сесію і токенів не буде з чим порівнювати.
 *
 * БД: sqlite :memory: форситься в tests/bootstrap.php + phpunit.xml (force=true),
 * плюс другий запобіжник у Tests\TestCase::setUp(). Реальний dev-mysql не
 * чіпається. LazilyRefreshDatabase — як у решті Feature-тестів цього форку.
 */
class Csrf419RecoveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Підміна канонічного CSRF-middleware на варіант, що НЕ вимикається
        // під phpunit. Container::make() у pipeline відрезолвить саме його.
        $this->app->bind(ValidateCsrfToken::class, function (Application $app) {
            return new class($app, $app->make(Encrypter::class)) extends ValidateCsrfToken
            {
                protected function runningUnitTests()
                {
                    // КЛЮЧ ТЕСТУ: примушуємо middleware ПРАЦЮВАТИ під тестами,
                    // інакше invalid _token мовчки проходить і 419 не виникає.
                    return false;
                }
            };
        });
    }

    /**
     * Звичайний web-POST (форма) з невалідним _token → graceful 302 redirect
     * назад на Referer + flash-помилка. НЕ мертвий 419-тупик.
     */
    public function test_invalid_token_on_web_form_redirects_back_not_419(): void
    {
        $this->startSession(); // у сесії з'явиться валідний токен для порівняння

        $response = $this
            ->from('https://gazu.test/cart')
            ->post('/cart/clear', [
                '_token' => 'definitely-not-the-session-token',
            ]);

        // Handler з bootstrap/app.php (звичайна форма):
        //   redirect()->to(Referer)->with('error', '…').
        $response->assertStatus(302);
        $response->assertRedirect('https://gazu.test/cart');
        $response->assertSessionHas('error', 'Сесія застаріла — спробуйте ще раз.');

        // САНІТІ: НЕ 419 (інакше тупик лишився / handler не спрацював).
        $this->assertNotSame(419, $response->getStatusCode());
    }

    /**
     * Web-POST без Referer → handler падає на fallback url('/')
     * (не-admin шлях ⇒ головна, а не /admin/login).
     */
    public function test_invalid_token_on_web_form_without_referer_falls_back_home(): void
    {
        $this->startSession();

        $response = $this->post('/cart/clear', [
            '_token' => 'bad-token-no-referer',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(url('/'));
        $response->assertSessionHas('error', 'Сесія застаріла — спробуйте ще раз.');
    }

    /**
     * POST на api/* (JSON) з невалідним токеном → 419 + JSON-повідомлення.
     * Гілка handler: $request->is('api/*') || $request->expectsJson().
     */
    public function test_invalid_token_on_api_json_returns_419_with_message(): void
    {
        $this->startSession();

        $response = $this->postJson('/api/callback', [
            '_token' => 'bad-token',
            'name' => 'Тест',
            'phone' => '+380501112233',
        ]);

        $response->assertStatus(419);
        $response->assertExactJson(['message' => 'Сесія застаріла, оновіть сторінку.']);
    }

    /**
     * Тонкий момент handler-а: гілка 419-JSON прив'язана САМЕ до
     * $request->expectsJson(), а НЕ до is('api/*'). Тож звичайний (не-JSON)
     * POST на api/*-шлях БЕЗ Accept: application/json НЕ матчить JSON-гілку
     * і граційно редиректиться як web-форма (302), а не віддає 419-тупик.
     *
     * Це фіксує контракт: 419-JSON отримує лише той, хто реально чекає JSON
     * (XHR/fetch з Accept), решта — м'який redirect. Захист від регресії, якби
     * хтось «спростив» handler до is('api/*') і зламав HTML-навігацію по api-URL.
     */
    public function test_invalid_token_on_api_path_without_json_redirects(): void
    {
        $this->startSession();

        $response = $this
            ->from('https://gazu.test/cart')
            ->post('/api/callback', [
                '_token' => 'bad-token',
                'name' => 'Тест',
                'phone' => '+380501112233',
            ]);

        // Не-JSON ⇒ web-гілка handler-а: 302 redirect + flash, НЕ 419.
        $response->assertStatus(302);
        $response->assertRedirect('https://gazu.test/cart');
        $response->assertSessionHas('error', 'Сесія застаріла — спробуйте ще раз.');
        $this->assertNotSame(419, $response->getStatusCode());
    }

    /**
     * Livewire-запит (X-Livewire) з невалідним токеном → 200 + reload-payload
     * з заголовком X-Livewire-Reload: stale-csrf (а НЕ 419/redirect).
     * Гілка handler: $request->is('livewire/*') || $request->hasHeader('X-Livewire').
     */
    public function test_invalid_token_on_livewire_returns_reload_payload(): void
    {
        $this->startSession();

        $response = $this->post('/cart/clear', [
            '_token' => 'bad-token',
        ], [
            'X-Livewire' => 'true',
            'Referer' => 'https://gazu.test/cart',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('X-Livewire-Reload', 'stale-csrf');
    }

    /**
     * САНІТІ самого харнесу: з ВАЛІДНИМ токеном сесії CSRF-перевірка проходить —
     * отже 419/302 у решті тестів спричинені саме невалідним токеном, а не тим,
     * що middleware завжди кидає. Головне — запит НЕ завершився CSRF-тупиком.
     */
    public function test_valid_session_token_passes_csrf(): void
    {
        $this->startSession();
        $token = session()->token();

        $response = $this->post('/cart/clear', [
            '_token' => $token,
        ]);

        // Що б не повернув контролер — це НЕ 419 і НЕ CSRF-flash «застаріла».
        // CSRF-шар пропустив запит далі.
        $this->assertNotSame(419, $response->getStatusCode());
        $this->assertNotSame(
            'Сесія застаріла — спробуйте ще раз.',
            session('error'),
            'З валідним токеном CSRF-handler не мав спрацьовувати.'
        );
    }
}
