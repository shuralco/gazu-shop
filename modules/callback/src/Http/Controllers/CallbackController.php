<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'nullable|string|max:100',
            'phone'  => 'required|string|min:7|max:32|regex:/^[\d\+\-\s\(\)]+$/',
            'source' => 'nullable|string|max:32',
        ], [
            'phone.required' => 'Введіть номер телефону',
            'phone.min'      => 'Номер телефону занадто короткий',
            'phone.regex'    => 'Невірний формат номера',
        ]);

        // Anti-spam: ліміт 3 на IP за 10 хвилин (через cache).
        $key = 'callback:'.$request->ip();
        $count = (int) \Cache::get($key, 0);
        if ($count >= 3) {
            return response()->json([
                'ok' => false,
                'message' => 'Забагато запитів. Спробуйте за 10 хвилин або зателефонуйте безпосередньо.',
            ], 429);
        }
        \Cache::put($key, $count + 1, now()->addMinutes(10));

        $req = CallbackRequest::create([
            'name'         => $data['name'] ?? null,
            'phone'        => $data['phone'],
            'source'       => $data['source'] ?? 'footer',
            'status'       => CallbackRequest::STATUS_NEW,
            'referrer_url' => mb_substr((string) $request->headers->get('referer', ''), 0, 500),
            'ip_address'   => $request->ip(),
            'user_agent'   => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        // Telegram-сповіщення менеджеру — швидкий push.
        try {
            $tg = app(\App\Services\TelegramService::class);
            if ($tg->isConfigured()) {
                $msg = "🔔 <b>НОВА заявка на дзвінок</b>\n\n"
                    ."📞 <b>".e($req->phone)."</b>\n"
                    .($req->name ? "👤 ".e($req->name)."\n" : '')
                    ."🌐 Джерело: <code>".e($req->source)."</code>\n"
                    .($req->referrer_url ? "🔗 ".e($req->referrer_url)."\n" : '')
                    ."🕒 ".($req->created_at?->format('H:i d.m.Y') ?? '')."\n\n"
                    ."<a href=\"".url('/admin/callback-requests/'.$req->id.'/edit')."\">Відкрити в адмінці</a>";
                $tg->send($msg);
            }
        } catch (\Throwable $e) {
            \Log::warning('Callback Telegram failed: '.$e->getMessage());
        }

        // Email сповіщення адміну — silent fail щоб не ламати UX.
        try {
            $adminEmail = \App\Models\DisplaySetting::get('email_admin_address')
                ?? config('mail.from.address');
            if (! empty($adminEmail)) {
                \Mail::to($adminEmail)->queue(new \App\Mail\TemplatedMail('callback.received', [
                    'callback' => [
                        'phone' => $req->phone,
                        'name' => $req->name ?: '—',
                        'source' => $req->source,
                        'referrer_url' => $req->referrer_url ?: '—',
                        'created_at' => $req->created_at?->format('d.m.Y H:i') ?: '',
                        'admin_url' => url('/admin/callback-requests/'.$req->id.'/edit'),
                    ],
                ]));
            }
        } catch (\Throwable $e) {
            \Log::warning('Callback email failed: '.$e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'id' => $req->id,
            'message' => 'Дякуємо! Передзвонимо протягом 5 хвилин у робочий час.',
        ]);
    }
}
