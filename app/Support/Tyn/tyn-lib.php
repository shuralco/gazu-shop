<?php
/**
 * Тин: підключення капчі «Тин» до PHP-сайтів (один файл, без залежностей).
 *
 * Сумісність: PHP 5.6 – 8.3. Функції мають префікс tyn_.
 *
 * Дві точки входу:
 *   tyn_widget($site_key, $base_url)           HTML віджета для форми;
 *   tyn_check($secret, $token, $options)       перевірка токена на сервері.
 *
 * Політика (м'який пропуск, режим soft): будь-яка спроба, навіть хибна, пропускає
 * форму. Збої самого сервісу пропускають форму теж (відмова у відкритий бік) і
 * лише пишуться в error_log, без секрету й токена.
 */

if (!defined('TYN_LIB_LOADED')) {
    define('TYN_LIB_LOADED', 1);

    /** Версія бібліотеки. */
    define('TYN_VERSION', '1.0.0');
    /** Тайм-аут з'єднання, секунди. */
    define('TYN_CONNECT_TIMEOUT', 1);
    /** Загальний тайм-аут запиту, секунди. */
    define('TYN_TOTAL_TIMEOUT', 2);
    /** Найбільша допустима довжина токена. */
    define('TYN_TOKEN_MAX', 512);
    /** Найбільший прийнятий розмір відповіді сервісу, байт. */
    define('TYN_RESPONSE_MAX', 65536);

    /**
     * Нормалізує адресу сервісу: лише https:// (для тестів ще http://127.0.0.1
     * і http://localhost). Повертає адресу без кінцевого слеша або порожній рядок.
     */
    function tyn_normalize_base($base_url)
    {
        if (!is_string($base_url)) {
            return '';
        }
        $base_url = rtrim(trim($base_url), '/');
        if ($base_url === '') {
            return '';
        }
        // Заборонені керівні символи, пробіли, логін/пароль в адресі.
        if (preg_match('/[\x00-\x20\x7f\\\\@]/', $base_url)) {
            return '';
        }
        if (preg_match('#^https://[^/?\#]+#i', $base_url)) {
            return $base_url;
        }
        if (preg_match('#^http://(127\.0\.0\.1|localhost)(:[0-9]{1,5})?(/|$)#i', $base_url)) {
            return $base_url;
        }
        return '';
    }

    /** Екранування для HTML-атрибута. */
    function tyn_esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * HTML віджета: контейнер і скрипт-завантажувач.
     * $form (необов'язково): тип форми для data-form (login, register, callback,
     * subscribe, contact, review, other); інше значення ігнорується.
     * Якщо ключ порожній або адреса недопустима, повертає порожній рядок.
     */
    function tyn_widget($site_key, $base_url, $form = '')
    {
        $base = tyn_normalize_base($base_url);
        $site_key = is_string($site_key) ? trim($site_key) : '';
        if ($base === '' || $site_key === '') {
            return '';
        }
        $form_attr = '';
        if (is_string($form) && in_array($form, array('login', 'register', 'callback', 'subscribe', 'contact', 'review', 'other'), true)) {
            $form_attr = ' data-form="' . $form . '"';
        }
        return '<div data-tyn data-key="' . tyn_esc($site_key) . '"' . $form_attr . '></div>'
            . '<script async src="' . tyn_esc($base . '/w/tyn.js') . '"></script>';
    }

    /** Один короткий рядок у журнал помилок: без секрету й без токена. */
    function tyn_log($code, $detail = '')
    {
        $line = 'tyn: ' . $code;
        if ($detail !== '') {
            $line .= ' (' . preg_replace('/[^A-Za-z0-9 _:.\/-]/', '', substr((string) $detail, 0, 80)) . ')';
        }
        @error_log($line);
    }

    /** Тексти для користувача за замовчуванням (можна перевизначити options['messages']). */
    function tyn_default_messages()
    {
        return array(
            'missing' => 'Увімкніть JavaScript і пройдіть перевірку',
            'rejected' => 'Перевірка застаріла, спробуйте ще раз',
            'not_attempted' => 'Увімкніть JavaScript і пройдіть перевірку',
            'incorrect' => 'Перевірку не пройдено, спробуйте ще раз',
        );
    }

    /**
     * Надсилає POST до {base}/v1/siteverify.
     * Повертає array('status' => int, 'body' => string, 'error' => string);
     * status 0 означає помилку мережі чи тайм-аут.
     */
    function tyn_http_post($url, $fields, $connect_timeout, $timeout, $content_type = 'application/x-www-form-urlencoded')
    {
        // Рядок надсилається як є (наприклад, готовий JSON), масив кодується як форма.
        $body = is_string($fields) ? $fields : http_build_query($fields, '', '&');
        $result = array('status' => 0, 'body' => '', 'error' => '');

        if (function_exists('curl_init') && !defined('TYN_NO_CURL')) {
            $ch = curl_init();
            if (!$ch) {
                $result['error'] = 'curl_init';
                return $result;
            }
            $buffer = '';
            $over = false;
            $opts = array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: ' . $content_type,
                    'Accept: application/json',
                    'Expect:', // без очікування 100-continue (старі libcurl чекають 1 с на тілах понад 1 КіБ)
                ),
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_CONNECTTIMEOUT_MS => (int) ($connect_timeout * 1000),
                CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
                CURLOPT_USERAGENT => 'tyn-php/' . TYN_VERSION,
            );
            if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
                $opts[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
            }
            // Обмеження розміру відповіді.
            $opts[CURLOPT_WRITEFUNCTION] = function ($handle, $chunk) use (&$buffer, &$over) {
                $buffer .= $chunk;
                if (strlen($buffer) > TYN_RESPONSE_MAX) {
                    $over = true;
                    return 0;
                }
                return strlen($chunk);
            };
            curl_setopt_array($ch, $opts);
            curl_exec($ch);
            $errno = curl_errno($ch);
            $result['status'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['body'] = $over ? '' : $buffer;
            if ($errno !== 0) {
                $result['status'] = 0;
                $result['error'] = 'curl ' . $errno;
            }
            if (PHP_VERSION_ID < 80000) {
                curl_close($ch); // з PHP 8.0 не потрібно, з 8.5 застаріло
            }
            return $result;
        }

        // Запасний шлях без cURL: потоки PHP. Зведений тайм-аут тримаємо самі
        // (file_get_contents у PHP 5.6 може перевищити його вдвічі), тому
        // запит HTTP/1.0 пишемо вручну з перевіркою часу в циклі читання.
        return tyn_http_post_stream($url, $body, $connect_timeout, $timeout, $content_type);
    }

    /** Запит без cURL: сокет із контекстом TLS і загальним терміном виконання. */
    function tyn_http_post_stream($url, $body, $connect_timeout, $timeout, $content_type = 'application/x-www-form-urlencoded')
    {
        $result = array('status' => 0, 'body' => '', 'error' => '');
        $deadline = microtime(true) + $timeout;
        $p = parse_url($url);
        if (!is_array($p) || !isset($p['host']) || !isset($p['scheme'])) {
            $result['error'] = 'url';
            return $result;
        }
        $tls = (strtolower($p['scheme']) === 'https');
        $port = isset($p['port']) ? (int) $p['port'] : ($tls ? 443 : 80);
        $path = (isset($p['path']) ? $p['path'] : '/') . (isset($p['query']) ? '?' . $p['query'] : '');
        $host = $p['host'];

        $context = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
            ),
        ));
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(
            ($tls ? 'ssl://' : 'tcp://') . $host . ':' . $port,
            $errno,
            $errstr,
            $connect_timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$fp) {
            $result['error'] = 'connect';
            return $result;
        }
        $request = "POST " . $path . " HTTP/1.0\r\n"
            . "Host: " . $host . (isset($p['port']) ? ':' . $p['port'] : '') . "\r\n"
            . "User-Agent: tyn-php/" . TYN_VERSION . "\r\n"
            . "Accept: application/json\r\n"
            . "Content-Type: " . $content_type . "\r\n"
            . "Content-Length: " . strlen($body) . "\r\n"
            . "Connection: close\r\n\r\n" . $body;
        stream_set_timeout($fp, 0, 200000);
        $written = 0;
        $len = strlen($request);
        while ($written < $len && microtime(true) < $deadline) {
            $n = @fwrite($fp, substr($request, $written));
            if ($n === false || $n === 0) {
                break;
            }
            $written += $n;
        }
        $raw = '';
        if ($written === $len) {
            while (!feof($fp) && microtime(true) < $deadline && strlen($raw) <= TYN_RESPONSE_MAX + 8192) {
                $chunk = @fread($fp, 8192);
                if ($chunk === false) {
                    break;
                }
                $raw .= $chunk;
            }
        }
        $complete = feof($fp);
        fclose($fp);
        if (!$complete) {
            $result['error'] = 'timeout';
            return $result;
        }
        $pos = strpos($raw, "\r\n\r\n");
        if ($pos === false || !preg_match('#^HTTP/\S+\s+(\d{3})#', $raw, $m)) {
            $result['error'] = 'response';
            return $result;
        }
        $result['status'] = (int) $m[1];
        $body_out = substr($raw, $pos + 4);
        $result['body'] = strlen($body_out) > TYN_RESPONSE_MAX ? '' : (string) $body_out;
        return $result;
    }

    /** Результат перевірки. */
    function tyn_result($allow, $reason, $correct, $message)
    {
        return array(
            'allow' => (bool) $allow,
            'reason' => $reason,
            'correct' => $correct,
            'message' => $message,
        );
    }

    /**
     * Перевіряє токен.
     *
     * $options (усе необов'язкове):
     *   base_url  адреса сервісу (https://…), обов'язкова на практиці;
     *   mode      'soft' (типово) або 'enforce' (потрібно ще й correct=true);
     *   messages  масив перевизначень текстів (ключі як у tyn_default_messages);
     *   connect_timeout, timeout  секунди (типово 1 і 2).
     *
     * Повертає array('allow', 'reason', 'correct', 'message'). Коди reason:
     *   ok, unavailable, service_error, config_error  дозволено;
     *   missing, not_attempted, rejected, incorrect   відхилено.
     */
    function tyn_check($secret, $token, $options = array())
    {
        if (!is_array($options)) {
            $options = array();
        }
        $messages = tyn_default_messages();
        if (isset($options['messages']) && is_array($options['messages'])) {
            $messages = array_merge($messages, $options['messages']);
        }
        $enforce = isset($options['mode']) && $options['mode'] === 'enforce';

        // 1. Токен: немає, порожній, не рядок.
        if (!is_string($token) || $token === '') {
            return tyn_result(false, 'missing', null, $messages['missing']);
        }
        // 2. Завантажувач не дочекався рамки.
        if ($token === 'unavailable') {
            tyn_log('widget unavailable');
            return tyn_result(true, 'unavailable', null, '');
        }
        // 3. Довжина й алфавіт: base64url і крапка; інше це «токена немає».
        if (strlen($token) > TYN_TOKEN_MAX || !preg_match('/^[A-Za-z0-9_.\-]+$/', $token)) {
            return tyn_result(false, 'missing', null, $messages['missing']);
        }

        // 4. Налаштування: хибні відкривають форму (відмова у відкритий бік).
        $base = tyn_normalize_base(isset($options['base_url']) ? $options['base_url'] : '');
        if ($base === '') {
            tyn_log('config error: bad base_url');
            return tyn_result(true, 'config_error', null, '');
        }
        if (!is_string($secret) || $secret === '') {
            tyn_log('config error: empty secret');
            return tyn_result(true, 'config_error', null, '');
        }

        $connect_timeout = isset($options['connect_timeout']) ? (float) $options['connect_timeout'] : TYN_CONNECT_TIMEOUT;
        $timeout = isset($options['timeout']) ? (float) $options['timeout'] : TYN_TOTAL_TIMEOUT;

        // 5. Запит до сервісу.
        $resp = tyn_http_post(
            $base . '/v1/siteverify',
            array('secret' => $secret, 'token' => $token),
            $connect_timeout,
            $timeout
        );
        if ($resp['status'] !== 200) {
            tyn_log('service error', $resp['status'] === 0 ? $resp['error'] : 'http ' . $resp['status']);
            return tyn_result(true, 'service_error', null, '');
        }
        $json = json_decode($resp['body'], true);
        if (!is_array($json) || !isset($json['ok']) || !is_bool($json['ok'])) {
            tyn_log('service error', 'bad json');
            return tyn_result(true, 'service_error', null, '');
        }

        // 6. Рішення.
        if ($json['ok'] === false) {
            return tyn_result(false, 'rejected', null, $messages['rejected']);
        }
        $correct = (isset($json['correct']) && is_bool($json['correct'])) ? $json['correct'] : null;
        if (!isset($json['attempted']) || $json['attempted'] !== true) {
            return tyn_result(false, 'not_attempted', $correct, $messages['not_attempted']);
        }
        if ($enforce && $correct !== true) {
            return tyn_result(false, 'incorrect', $correct, $messages['incorrect']);
        }
        return tyn_result(true, 'ok', $correct, '');
    }

    /**
     * Перевірка токена з поточного POST-запиту одним викликом:
     *   $r = tyn_check_request($secret, array('base_url' => $url));
     *   if (!$r['allow']) { ...показати $r['message']... }
     */
    function tyn_check_request($secret, $options = array())
    {
        $token = isset($_POST['tyn_token']) ? $_POST['tyn_token'] : null;
        return tyn_check($secret, $token, $options);
    }

    /**
     * Перевірка зв'язку з сервісом (для кнопки в адмінці): true, якщо прийшла
     * будь-яка HTTP-відповідь. Нічого не розкриває, секрет не потрібен.
     */
    function tyn_ping($base_url)
    {
        $base = tyn_normalize_base($base_url);
        if ($base === '') {
            return false;
        }
        $resp = tyn_http_post(
            $base . '/v1/siteverify',
            array('secret' => 'ping', 'token' => 'ping'),
            TYN_CONNECT_TIMEOUT,
            TYN_TOTAL_TIMEOUT
        );
        return $resp['status'] > 0;
    }
    /** Чи токен має допустимий вигляд (довжина й алфавіт), без звернення до сервісу. */
    function tyn_token_ok($token)
    {
        return is_string($token) && $token !== '' && strlen($token) <= TYN_TOKEN_MAX
            && preg_match('/^[A-Za-z0-9_.\-]+$/', $token) === 1;
    }

    /**
     * Поле-пастка для ботів: звичайний input, прихований CSS-класом (не type=hidden).
     * Людина його не бачить і не заповнює. $name: ім'я поля (латиниця, цифри, _).
     */
    function tyn_honeypot($name)
    {
        if (!is_string($name) || !preg_match('/^[a-z][a-z0-9_]{2,30}$/', $name)) {
            return '';
        }
        return '<style>.tyn-hp{position:absolute!important;left:-10000px!important;top:auto!important;'
            . 'width:1px!important;height:1px!important;overflow:hidden!important}</style>'
            . '<div class="tyn-hp" aria-hidden="true"><input type="text" name="' . tyn_esc($name)
            . '" value="" tabindex="-1" autocomplete="off"></div>';
    }

    /**
     * Подія для навчання сервісу: POST {base}/v1/event. Тайм-аут 1 с загалом.
     * Будь-який збій мовчазний (ні виключень, ні журналу), форму це не зачіпає.
     *
     * $event: form_submit | form_no_token | honeypot.
     * $fields (інші ключі ігноруються, тож вміст форми піти не може):
     *   token (допустимий вигляд) або chain (hex до 64), form (латиниця до 32),
     *   fill_s (секунди, ціле), honeypot (0/1).
     * Повертає true, якщо сервіс відповів 200.
     */
    function tyn_event($secret, $base_url, $event, $fields)
    {
        try {
            if (!in_array($event, array('form_submit', 'form_no_token', 'honeypot'), true)) {
                return false;
            }
            $base = tyn_normalize_base($base_url);
            if ($base === '' || !is_string($secret) || $secret === '' || !is_array($fields)) {
                return false;
            }
            $post = array('secret' => $secret, 'event' => $event);
            if (isset($fields['token']) && tyn_token_ok($fields['token'])) {
                $post['token'] = $fields['token'];
            } elseif (isset($fields['chain']) && is_string($fields['chain']) && preg_match('/^[a-f0-9]{8,64}$/', $fields['chain'])) {
                $post['chain'] = $fields['chain'];
            } else {
                return false;
            }
            $post['form'] = (isset($fields['form']) && is_string($fields['form']) && preg_match('/^[a-z0-9_]{1,32}$/', $fields['form']))
                ? $fields['form'] : 'other';
            $fill = isset($fields['fill_s']) ? (int) $fields['fill_s'] : 0;
            $post['fill_s'] = max(0, min(86400, $fill));
            $post['honeypot'] = empty($fields['honeypot']) ? 0 : 1;

            $resp = tyn_http_post($base . '/v1/event', $post, 1, 1);
            return $resp['status'] === 200;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }
    // ---------------------------------------------------------------------
    // Облік запитів сайту: POST {base}/v1/traffic (протокол, розділ «Облік запитів»).
    // Запис у локальний буфер без мережі (tyn_traffic_record), надсилання пачкою
    // після відповіді відвідувачеві (tyn_traffic_flush). Будь-який збій мовчазний.
    // ---------------------------------------------------------------------

    /** Найбільше записів у пачці. */
    define('TYN_TRAFFIC_MAX_BATCH', 500);
    /** Найбільший розмір тіла пачки, байт (протокол дозволяє 256 КіБ). */
    define('TYN_TRAFFIC_MAX_BODY', 245760);
    /** Найменший інтервал між надсиланнями, секунди. */
    define('TYN_TRAFFIC_MIN_INTERVAL', 2);
    /** Записи й буфер старші за цей вік (секунди) відкидаються. */
    define('TYN_TRAFFIC_MAX_AGE', 600);
    /** Найбільший розмір буфера, байт (2 МіБ). */
    define('TYN_TRAFFIC_MAX_BYTES', 2097152);

    /** Шляхи файлів буфера в теці $dir. */
    function tyn_traffic_files($dir)
    {
        $dir = rtrim((string) $dir, '/\\') . '/';
        return array(
            'dir' => $dir,
            'buf' => $dir . 'tyn-traffic.buf',    // сюди дописують запити
            'work' => $dir . 'tyn-traffic.work',  // забрана для надсилання пачка й залишок
            'lock' => $dir . 'tyn-traffic.lock',  // замок і час останнього надсилання
            'err' => $dir . 'tyn-traffic.err',    // позначка часу останнього рядка в журналі
            'tmp' => $dir . 'tyn-traffic.tmp',
        );
    }

    /** Двійковий вигляд адреси (4 байти для IPv4, 16 для IPv6; IPv4-mapped зводиться до IPv4) або false. */
    function tyn_traffic_bin($ip)
    {
        if (!is_string($ip)) {
            return false;
        }
        $ip = trim($ip);
        $pos = strpos($ip, '%');
        if ($pos !== false) {
            $ip = substr($ip, 0, $pos);
        }
        if ($ip === '' || strlen($ip) > 45) {
            return false;
        }
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        if (strlen($bin) === 16 && substr($bin, 0, 12) === "\0\0\0\0\0\0\0\0\0\0\xff\xff") {
            $bin = substr($bin, 12);
        }
        return $bin;
    }

    /** Мережа відвідувача: /24 для IPv4, /48 для IPv6; порожній рядок, якщо адреса некоректна. */
    function tyn_traffic_net($ip)
    {
        $bin = tyn_traffic_bin($ip);
        if ($bin === false) {
            return '';
        }
        if (strlen($bin) === 4) {
            return ord($bin[0]) . '.' . ord($bin[1]) . '.' . ord($bin[2]) . '.0/24';
        }
        $net = @inet_ntop(substr($bin, 0, 6) . "\0\0\0\0\0\0\0\0\0\0");
        return $net === false ? '' : $net . '/48';
    }

    /** Чи адреса входить до переліку (кожен елемент: адреса або мережа a.b.c.d/n). */
    function tyn_traffic_in_list($ip, $list)
    {
        $bin = tyn_traffic_bin($ip);
        if ($bin === false || !is_array($list)) {
            return false;
        }
        foreach ($list as $entry) {
            if (!is_string($entry)) {
                continue;
            }
            $bits = null;
            $slash = strpos($entry, '/');
            if ($slash !== false) {
                $bits = (int) substr($entry, $slash + 1);
                $entry = substr($entry, 0, $slash);
            }
            $net = tyn_traffic_bin($entry);
            if ($net === false || strlen($net) !== strlen($bin)) {
                continue;
            }
            $max = strlen($net) * 8;
            if ($bits === null || $bits > $max) {
                $bits = $max;
            }
            if ($bits < 0) {
                continue;
            }
            $bytes = (int) floor($bits / 8);
            if ($bytes > 0 && substr($bin, 0, $bytes) !== substr($net, 0, $bytes)) {
                continue;
            }
            $rest = $bits % 8;
            if ($rest > 0) {
                $mask = (0xff << (8 - $rest)) & 0xff;
                if ((ord($bin[$bytes]) & $mask) !== (ord($net[$bytes]) & $mask)) {
                    continue;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Адреса відвідувача. Типово REMOTE_ADDR. Заголовок проксі береться лише
     * якщо ввімкнено opts['proxy_header'] ('X-Forwarded-For' або 'CF-Connecting-IP')
     * І REMOTE_ADDR входить до opts['trusted_proxies'] (масив адрес і мереж).
     * Для X-Forwarded-For обирається перша справа адреса, що не є довіреним проксі.
     */
    function tyn_traffic_client_ip(array $server, array $opts = array())
    {
        $remote = (isset($server['REMOTE_ADDR']) && is_string($server['REMOTE_ADDR'])) ? trim($server['REMOTE_ADDR']) : '';
        $header = (isset($opts['proxy_header']) && is_string($opts['proxy_header'])) ? strtolower(trim($opts['proxy_header'])) : '';
        $trusted = (isset($opts['trusted_proxies']) && is_array($opts['trusted_proxies'])) ? $opts['trusted_proxies'] : array();
        if ($header === '' || !$trusted || !tyn_traffic_in_list($remote, $trusted)) {
            return $remote;
        }
        if ($header === 'cf-connecting-ip') {
            $v = isset($server['HTTP_CF_CONNECTING_IP']) && is_string($server['HTTP_CF_CONNECTING_IP']) ? trim($server['HTTP_CF_CONNECTING_IP']) : '';
            return tyn_traffic_bin($v) !== false ? $v : $remote;
        }
        if ($header === 'x-forwarded-for') {
            $v = isset($server['HTTP_X_FORWARDED_FOR']) && is_string($server['HTTP_X_FORWARDED_FOR']) ? $server['HTTP_X_FORWARDED_FOR'] : '';
            $parts = array_reverse(explode(',', $v));
            foreach ($parts as $part) {
                $part = trim($part);
                if (tyn_traffic_bin($part) === false) {
                    return $remote; // сміття в ланцюжку: довіряти нічому
                }
                if (!tyn_traffic_in_list($part, $trusted)) {
                    return $part;
                }
            }
        }
        return $remote;
    }

    /** Лише друковані ASCII без пробілів, обрізано до $max байт. */
    function tyn_traffic_clean($value, $max, $keep_space = false)
    {
        $value = is_string($value) ? $value : '';
        $value = preg_replace($keep_space ? '/[^\x20-\x7e]/' : '/[^\x21-\x7e]/', '', $value);
        return (string) substr($value, 0, $max); // до PHP 8 substr('') повертає false
    }

    /** Розбирає рядок запиту у масив (ключ => останнє значення, декодоване); без побічних ефектів. */
    function tyn_traffic_query($qs)
    {
        $out = array();
        if (!is_string($qs) || $qs === '') {
            return $out;
        }
        foreach (explode('&', substr($qs, 0, 4000)) as $pair) {
            if ($pair === '') {
                continue;
            }
            $eq = strpos($pair, '=');
            $k = rawurldecode(str_replace('+', ' ', $eq === false ? $pair : substr($pair, 0, $eq)));
            $v = $eq === false ? '' : rawurldecode(str_replace('+', ' ', substr($pair, $eq + 1)));
            $out[strtolower($k)] = $v;
        }
        return $out;
    }

    /** utm_*: до 64 символів, лише [A-Za-z0-9_.-] (решта символів стає «_»), нижній регістр (як на сервері). */
    function tyn_traffic_utm($value)
    {
        $value = trim(is_string($value) ? $value : '');
        if ($value === '') {
            return '';
        }
        if (preg_match('/^./su', $value) && preg_match_all('/./su', $value, $m)) {
            $chars = array_slice($m[0], 0, 64);
        } else {
            $chars = str_split(substr($value, 0, 64)); // не UTF-8: по байтах
        }
        $out = '';
        foreach ($chars as $c) {
            $out .= preg_match('/^[A-Za-z0-9_.\-]$/', $c) ? strtolower($c) : '_';
        }
        return $out;
    }

    /** Лише ТИП рекламної мітки в запиті: g (gclid/gbraid/wbraid), f (fbclid), y (yclid), t (ttclid), m (msclkid). Значення не повертається. */
    function tyn_traffic_click_type(array $query)
    {
        $map = array('gclid' => 'g', 'gbraid' => 'g', 'wbraid' => 'g', 'fbclid' => 'f', 'yclid' => 'y', 'ttclid' => 't', 'msclkid' => 'm');
        foreach ($map as $name => $type) {
            if (isset($query[$name]) && $query[$name] !== '') {
                return $type;
            }
        }
        return '';
    }

    /** Тип сторінки: один із десяти відомих значень сервера; невідоме дає other, порожнє лишається порожнім. */
    function tyn_traffic_page_type($value)
    {
        $v = strtolower(trim(is_string($value) ? $value : ''));
        if ($v === '') {
            return '';
        }
        return in_array($v, array('home', 'category', 'product', 'search', 'cart', 'checkout', 'success', 'account', 'info', 'other'), true) ? $v : 'other';
    }

    /**
     * Анотації поточного запиту (page_type, event, amount, currency): модуль сайту викликає її де завгодно під час запиту,
     * tyn_traffic_record підхоплює їх. Без аргументів повертає накопичене; false скидає. Без мережі, без винятків.
     */
    function tyn_traffic_annotate($fields = null)
    {
        static $acc = array();
        if ($fields === false) {
            $acc = array(); // скинути (довгоживучі процеси, тести)
        } elseif (is_array($fields)) {
            foreach (array('page_type', 'event', 'amount', 'currency', 'cid', 'uid') as $k) {
                if (isset($fields[$k])) {
                    $acc[$k] = $fields[$k];
                }
            }
        }
        return $acc;
    }

    /** Чи шлях веде до статичного файла (зображення, стилі, скрипти, шрифти, map, ico). */
    function tyn_traffic_is_static($path)
    {
        $slash = strrpos($path, '/');
        $name = $slash === false ? $path : substr($path, $slash + 1);
        $dot = strrpos($name, '.');
        if ($dot === false) {
            return false;
        }
        $ext = strtolower(substr($name, $dot + 1));
        return in_array($ext, array(
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'bmp', 'ico', 'css', 'js', 'mjs', 'map',
            'woff', 'woff2', 'ttf', 'otf', 'eot',
        ), true);
    }

    /**
     * Збирає один запис обліку з $server (типово $_SERVER) строго за протоколом.
     * Повертає масив полів або null, якщо запит не обліковується (статичний
     * файл, відвідувача не визначено).
     * $opts: proxy_header, trusted_proxies (див. tyn_traffic_client_ip).
     */
    function tyn_traffic_build(array $server, $status = null, $startedAt = null, array $opts = array())
    {
        $clientIp = tyn_traffic_client_ip($server, $opts);
        $net = tyn_traffic_net($clientIp);
        if ($net === '') {
            return null;
        }

        // Шлях без рядка запиту й фрагмента.
        $uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
        $cut = strcspn($uri, '?#');
        // Рядок запиту сам НЕ передається: з нього беруться лише utm_* (очищені) і тип рекламної мітки (без значення).
        $qs = '';
        if (isset($uri[$cut]) && $uri[$cut] === '?') {
            $qs = (string) substr($uri, $cut + 1, strcspn($uri, '#', $cut + 1));
        }
        $path = tyn_traffic_clean((string) substr($uri, 0, $cut), 200);
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
            $path = substr($path, 0, 200);
        }
        if (tyn_traffic_is_static($path)) {
            return null;
        }

        $method = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? strtoupper($server['REQUEST_METHOD']) : 'GET';
        if (!in_array($method, array('GET', 'POST', 'HEAD'), true)) {
            $method = 'OTHER';
        }

        if ($status === null && function_exists('http_response_code')) {
            $status = http_response_code();
        }
        $status = (int) $status;
        if ($status < 100 || $status > 599) {
            $status = 200;
        }

        // Реферер: лише хост; порожньо для власного сайту.
        $ref = '';
        if (!empty($server['HTTP_REFERER']) && is_string($server['HTTP_REFERER'])) {
            $host = @parse_url(substr($server['HTTP_REFERER'], 0, 2000), PHP_URL_HOST);
            if (is_string($host)) {
                $ref = strtolower(tyn_traffic_clean($host, 100));
                if (!preg_match('/^[a-z0-9._-]+$/', $ref)) {
                    $ref = '';
                }
            }
        }
        if ($ref !== '') {
            $own = isset($server['HTTP_HOST']) && is_string($server['HTTP_HOST']) ? strtolower($server['HTTP_HOST']) : '';
            $own = preg_replace('/:\d+$/', '', $own);
            $norm = function ($h) {
                return preg_replace('/^www\./', '', rtrim($h, '.'));
            };
            if ($own !== '' && $norm($own) === $norm($ref)) {
                $ref = '';
            }
        }

        $lang = '';
        if (!empty($server['HTTP_ACCEPT_LANGUAGE']) && is_string($server['HTTP_ACCEPT_LANGUAGE'])
            && preg_match('/^\s*([A-Za-z]{2})/', $server['HTTP_ACCEPT_LANGUAGE'], $m)) {
            $lang = strtolower($m[1]);
        }

        $ms = 0;
        if ($startedAt === null && isset($server['REQUEST_TIME_FLOAT'])) {
            $startedAt = $server['REQUEST_TIME_FLOAT'];
        }
        if (is_numeric($startedAt) && $startedAt > 0) {
            $ms = (int) round((microtime(true) - (float) $startedAt) * 1000);
            $ms = max(0, min(60000, $ms));
        }

        $xhr = 0;
        if ((isset($server['HTTP_X_REQUESTED_WITH']) && is_string($server['HTTP_X_REQUESTED_WITH'])
                && strtolower($server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($server['HTTP_SEC_FETCH_MODE']) && $server['HTTP_SEC_FETCH_MODE'] === 'cors')) {
            $xhr = 1;
        }

        $query = tyn_traffic_query($qs);
        $extra = array();
        foreach (array('us' => 'utm_source', 'um' => 'utm_medium', 'uc' => 'utm_campaign') as $short => $name) {
            $v = isset($query[$name]) ? tyn_traffic_utm($query[$name]) : '';
            if ($v !== '') {
                $extra[$short] = $v;
            }
        }
        $cid = tyn_traffic_click_type($query);
        if ($cid !== '') {
            $extra['cid'] = $cid;
        }
        // Поля з модуля сайту: $opts (page_type, event, amount, currency) або tyn_traffic_annotate().
        $ann = array_merge(tyn_traffic_annotate(), array_intersect_key($opts, array_flip(array('page_type', 'event', 'amount', 'currency', 'cid', 'uid'))));
        $pt = isset($ann['page_type']) ? tyn_traffic_page_type($ann['page_type']) : '';
        if ($pt !== '') {
            $extra['pt'] = $pt;
        }
        $ev = isset($ann['event']) && is_string($ann['event']) ? trim($ann['event']) : '';
        if ($ev === 'cart_add') {
            $extra['ev'] = 'cart_add';
        } elseif ($ev === 'order') {
            $extra['ev'] = 'order';
            $amt = isset($ann['amount']) && is_numeric($ann['amount']) ? (float) $ann['amount'] : 0;
            $amt = ($amt >= 0 && $amt <= 1000000000000) ? (int) round($amt) : 0;
            $cur = isset($ann['currency']) && is_string($ann['currency']) ? strtoupper(trim($ann['currency'])) : '';
            $extra['amt'] = $amt;
            if (preg_match('/^[A-Z]{3}$/', $cur)) {
                $extra['cur'] = $cur;
            }
        }

        // Рішення власника 2026-11-17: передається повна публічна адреса (поруч із net), постійний ідентифікатор браузера
        // (cookie tyn_cid; ключ vc, бо cid тут зайнятий типом рекламної мітки) і ідентифікатор покупця.
        $pub = tyn_public_ip($clientIp);
        if ($pub !== '') {
            $extra[TYN_TRACK_F_IP] = $pub;
        }
        $cuid = isset($ann['cid']) && is_string($ann['cid']) && preg_match('/^[0-9a-f]{32}$/', $ann['cid']) ? $ann['cid'] : tyn_cid_from_server($server);
        if ($cuid !== '') {
            $extra[TYN_TRAFFIC_F_CID] = $cuid;
        }
        $uid = isset($ann['uid']) ? tyn_track_id($ann['uid']) : null;
        if ($uid !== null) {
            $extra[TYN_TRACK_F_UID] = $uid;
        }

        $rec = array(
            'ts' => time(),
            'm' => $method,
            'p' => $path,
            's' => $status,
            'net' => $net,
            'ua' => tyn_traffic_clean(isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : '', 512, true),
            'ref' => $ref,
            'lang' => $lang,
            'ms' => $ms,
            'ck' => (isset($server['HTTP_COOKIE']) && is_string($server['HTTP_COOKIE']) && trim($server['HTTP_COOKIE']) !== '') ? 1 : 0,
            'xhr' => $xhr,
        );
        return $rec + $extra;
    }

    /** Дописує рядок у файл під замком; якщо файл тим часом забрали (перейменували), відкриває знову. */
    function tyn_traffic_append($file, $line)
    {
        for ($i = 0; $i < 3; $i++) {
            $old = umask(0077);
            $fp = @fopen($file, 'ab');
            umask($old);
            if (!$fp) {
                return false;
            }
            if (!@flock($fp, LOCK_EX)) {
                fclose($fp);
                return false;
            }
            // Той самий файл за шляхом? Інакше його щойно забрав tyn_traffic_flush.
            $a = fstat($fp);
            clearstatcache(true, $file);
            $b = @stat($file);
            if ($b === false || $a['ino'] != $b['ino'] || $a['dev'] != $b['dev']) {
                flock($fp, LOCK_UN);
                fclose($fp);
                continue;
            }
            if ($a['size'] >= TYN_TRAFFIC_MAX_BYTES) {
                flock($fp, LOCK_UN);
                fclose($fp);
                return false;
            }
            $ok = (@fwrite($fp, $line . "\n") === strlen($line) + 1);
            @fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return $ok;
        }
        return false;
    }

    /**
     * Додає один запис про поточний запит у буфер (рядок JSON). Жодної мережі.
     * Повертає true, якщо запис додано; false, якщо запит не обліковується
     * (статичний файл, невідома адреса відвідувача) або запис не вдався.
     */
    function tyn_traffic_record($bufferDir, $server = null, $status = null, $startedAt = null, $opts = array())
    {
        try {
            if (!is_string($bufferDir) || $bufferDir === '') {
                return false;
            }
            // $server: масив або null ($_SERVER); тип не вказано навмисно (PHP 5.6 не знає ?array).
            if (!is_array($server)) {
                $server = isset($_SERVER) && is_array($_SERVER) ? $_SERVER : array();
            }
            $rec = tyn_traffic_build($server, $status, $startedAt, is_array($opts) ? $opts : array());
            if ($rec === null) {
                return false;
            }
            $line = json_encode($rec, JSON_UNESCAPED_SLASHES);
            if (!is_string($line) || $line === '') {
                return false;
            }
            $f = tyn_traffic_files($bufferDir);
            if (!is_dir($f['dir'])) {
                if (!@mkdir($f['dir'], 0700, true) && !is_dir($f['dir'])) {
                    return false;
                }
                @file_put_contents($f['dir'] . '.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
            }
            return tyn_traffic_append($f['buf'], $line);
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Один рядок у журнал помилок не частіше ніж раз на 10 хв (без секрету й ключа). */
    function tyn_traffic_fail($f, $detail)
    {
        $t = @filemtime($f['err']);
        if ($t !== false && (time() - $t) < TYN_TRAFFIC_MAX_AGE) {
            return;
        }
        @touch($f['err']);
        tyn_log('traffic', $detail);
    }

    /** Залишок (необроблені рядки) назад у робочий файл атомарно; порожній залишок видаляє файл. */
    function tyn_traffic_store_rest($f, $rest)
    {
        if (!$rest) {
            @unlink($f['work']);
            return;
        }
        $old = umask(0077);
        $ok = @file_put_contents($f['tmp'], implode("\n", $rest) . "\n", LOCK_EX);
        umask($old);
        if ($ok !== false) {
            @rename($f['tmp'], $f['work']);
        }
    }

    /**
     * Забирає буфер: або недоправлену раніше робочу пачку, або атомарно
     * перейменовує буфер у робочий файл (під замком, тож дописувачі не губляться).
     * Повертає масив рядків або порожній масив.
     */
    function tyn_traffic_take($f, $now)
    {
        if (!is_file($f['work'])) {
            $fp = @fopen($f['buf'], 'r+b');
            if (!$fp) {
                return array();
            }
            if (!@flock($fp, LOCK_EX)) {
                fclose($fp);
                return array();
            }
            $st = fstat($fp);
            $renamed = false;
            if ($st['size'] > TYN_TRAFFIC_MAX_BYTES || ($now - $st['mtime']) > TYN_TRAFFIC_MAX_AGE) {
                @unlink($f['buf']); // завеликий чи застарілий буфер відкидається
            } elseif ($st['size'] > 0) {
                $renamed = @rename($f['buf'], $f['work']);
            }
            flock($fp, LOCK_UN);
            fclose($fp);
            if (!$renamed) {
                return array();
            }
        }
        clearstatcache(true, $f['work']);
        $size = @filesize($f['work']);
        if ($size === false || $size > TYN_TRAFFIC_MAX_BYTES) {
            @unlink($f['work']);
            return array();
        }
        $raw = @file_get_contents($f['work']);
        if (!is_string($raw) || $raw === '') {
            @unlink($f['work']);
            return array();
        }
        return explode("\n", $raw);
    }

    /**
     * Надсилає накопичені записи однією пачкою (до 500) на {serviceUrl}/v1/traffic.
     * Нічого не робить, якщо від останнього надсилання минуло менше заданого інтервалу (типово 2 с, межі 1–30), буфера
     * немає або інший процес уже надсилає (замок). Тайм-аут 1 с, з'єднання 1 с.
     * Залишок понад 500 лишається на наступний раз; буфер старший за 10 хв чи
     * більший за 2 МіБ відкидається. Викликати після віддачі відповіді.
     *
     * $opts (необов'язково): now (час, для тестів), min_interval (типово 2, підтискається до 1–30; 0 лише для тестів).
     * Повертає true, якщо сервіс прийняв пачку; будь-який збій тихий (false).
     */
    function tyn_traffic_flush($bufferDir, $serviceUrl, $key, $secret, $opts = array())
    {
        $lock = null;
        try {
            if (!is_array($opts)) {
                $opts = array();
            }
            $base = tyn_normalize_base($serviceUrl);
            if ($base === '' || !is_string($bufferDir) || $bufferDir === ''
                || !is_string($key) || $key === '' || !is_string($secret) || $secret === '') {
                return false;
            }
            $f = tyn_traffic_files($bufferDir);
            clearstatcache();
            if (!is_file($f['buf']) && !is_file($f['work'])) {
                return false;
            }
            $now = isset($opts['now']) ? (int) $opts['now'] : time();
            // Інтервал: типово 2 с; задане значення 1–30 (менше й більше підтискається; 0 лише для тестів).
            $interval = TYN_TRAFFIC_MIN_INTERVAL;
            if (isset($opts['min_interval'])) {
                $interval = (int) $opts['min_interval'];
                $interval = $interval <= 0 ? 0 : max(1, min(30, $interval));
            }

            // Замок надсилання: один процес за раз; заодно тут час останнього надсилання.
            $old = umask(0077);
            $lock = @fopen($f['lock'], 'c+b');
            umask($old);
            if (!$lock) {
                return false;
            }
            if (!@flock($lock, LOCK_EX | LOCK_NB)) {
                fclose($lock);
                $lock = null;
                return false;
            }
            $last = (int) trim((string) fread($lock, 32));
            if ($last > 0 && ($now - $last) >= 0 && ($now - $last) < $interval) {
                return false;
            }
            ftruncate($lock, 0);
            rewind($lock);
            fwrite($lock, (string) $now);
            fflush($lock);

            $lines = tyn_traffic_take($f, $now);
            if (!$lines) {
                return false;
            }
            $batch = array();
            $rest = array();
            $bytes = 0;
            foreach ($lines as $line) {
                if ($line === '') {
                    continue;
                }
                if (count($batch) >= TYN_TRAFFIC_MAX_BATCH || $bytes + strlen($line) > TYN_TRAFFIC_MAX_BODY) {
                    $rest[] = $line; // у пачку не влізло: на наступний раз
                    continue;
                }
                $rec = json_decode($line, true);
                if (!is_array($rec) || !isset($rec['ts']) || !is_int($rec['ts']) || ($now - $rec['ts']) > TYN_TRAFFIC_MAX_AGE) {
                    continue; // зіпсований або застарілий запис
                }
                $batch[] = $line;
                $bytes += strlen($line) + 1;
            }
            if (!$batch) {
                tyn_traffic_store_rest($f, $rest);
                return false;
            }
            $body = '{"key":' . json_encode($key) . ',"secret":' . json_encode($secret) . ',"v":1,"events":[' . implode(',', $batch) . ']}';
            $resp = tyn_http_post($base . '/v1/traffic', $body, 1, 1, 'application/json');
            $json = ($resp['status'] === 200) ? json_decode($resp['body'], true) : null;
            if (is_array($json) && isset($json['ok']) && $json['ok'] === true) {
                tyn_traffic_store_rest($f, $rest);
                return true;
            }
            // Збій: робочий файл лишається, наступна спроба не раніше ніж через інтервал;
            // записи доживають до 10 хв (тоді відкидаються за віком).
            tyn_traffic_fail($f, $resp['status'] === 0 ? $resp['error'] : 'http ' . $resp['status']);
            return false;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        } finally {
            if (is_resource($lock)) {
                @flock($lock, LOCK_UN);
                @fclose($lock);
            }
        }
    }

    /** Прибирає буфер і службові файли (при вимкненні чи видаленні модуля). */
    function tyn_traffic_clear($bufferDir)
    {
        try {
            if (!is_string($bufferDir) || $bufferDir === '') {
                return false;
            }
            $f = tyn_traffic_files($bufferDir);
            foreach (array('buf', 'work', 'lock', 'err', 'tmp') as $k) {
                @unlink($f[$k]);
            }
            @unlink($f['dir'] . '.htaccess');
            @rmdir($f['dir']);
            return true;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    // ---------------------------------------------------------------------
    // Заслін на весь сайт: сторінка з віджетом для відвідувача без позначки проходу.
    // Чисті функції (без OpenCart), щоб правила перевірялись тестами. Рішення
    // tyn_gate_decide() не торкається мережі й бази; будь-яка помилка означає «не показувати».
    // ---------------------------------------------------------------------

    /** Назва cookie позначки проходу. */
    define('TYN_GATE_COOKIE', 'tyn_pass');
    /** Скільки секунд діє «коротка» позначка (збій сервісу, unavailable), щоб не зациклити. */
    define('TYN_GATE_SHORT_TTL', 300);

    /** Боти, яким заслін не показується (підрядки User-Agent, нижній регістр). Доповнюються налаштуванням. */
    function tyn_gate_default_bots()
    {
        return array(
            'googlebot', 'google-inspectiontool', 'storebot-google', 'adsbot-google', 'mediapartners-google',
            'google-merchant', 'googleother', 'feedfetcher-google', 'apis-google', 'google-read-aloud', 'google-site-verification',
            'bingbot', 'bingpreview', 'msnbot', 'adidxbot',
            'duckduckbot', 'duckduckgo',
            'facebookexternalhit', 'facebot', 'meta-externalagent', 'meta-externalfetcher',
            'telegrambot', 'viber', 'twitterbot', 'pinterest', 'applebot',
            'linkedinbot', 'whatsapp', 'slackbot', 'skypeuripreview', 'discordbot',
            'hotline', 'prom.ua', 'promua', 'rozetka', 'price.ua', 'priceua',
        );
    }

    /** Перелік рядків з тексту (по одному в рядку або через кому/крапку з комою), нижній регістр, без порожніх. */
    function tyn_gate_list($text, $lower = true)
    {
        $out = array();
        if (!is_string($text)) {
            return $out;
        }
        foreach (preg_split('/[\r\n,;]+/', $text, -1, PREG_SPLIT_NO_EMPTY) as $item) {
            $item = trim($item);
            if ($item !== '' && strlen($item) <= 100) {
                $out[] = $lower ? strtolower($item) : $item;
            }
        }
        return $out;
    }

    /** Чи User-Agent належить дозволеному боту (типовий перелік плюс $extra). */
    function tyn_gate_is_allowed_bot($ua, $extra = array())
    {
        if (!is_string($ua) || $ua === '') {
            return false;
        }
        $ua = strtolower($ua);
        foreach (array_merge(tyn_gate_default_bots(), is_array($extra) ? $extra : array()) as $needle) {
            if (is_string($needle) && $needle !== '' && strpos($ua, strtolower($needle)) !== false) {
                return true;
            }
        }
        return false;
    }

    /** Кеш перевірки справжності ботів: скільки секунд тримати «справжній», «підробний» і «невідомо» (збій DNS), розмір файла. */
    define('TYN_BOT_TTL_OK', 86400);
    define('TYN_BOT_TTL_FAKE', 3600);
    define('TYN_BOT_TTL_UNKNOWN', 300);
    define('TYN_BOT_CACHE_MAX', 262144);
    /** Один DNS-запит чекає не довше цього (с), усі запити однієї перевірки разом не довше TYN_DNS_BUDGET. */
    define('TYN_DNS_TIMEOUT', 1.0);
    define('TYN_DNS_BUDGET', 2.0);

    /**
     * Боти, чию справжність можна перевірити: [підрядки User-Agent, дозволені домени PTR]. Лише те, що пошукова система
     * документує (reverse DNS + пряме підтвердження). Решта (facebookexternalhit, TelegramBot, DuckDuckBot, Hotline, Prom
     * тощо) лишається за UA: для них немає документованого PTR-правила.
     */
    function tyn_gate_bot_rules()
    {
        return array(
            array(array('googlebot', 'google-inspectiontool', 'storebot-google', 'adsbot-google', 'mediapartners-google', 'googleother'),
                array('googlebot.com', 'google.com')),
            array(array('bingbot', 'msnbot', 'adidxbot'), array('search.msn.com')),
            array(array('applebot'), array('applebot.apple.com')),
            array(array('yandex'), array('yandex.ru', 'yandex.net', 'yandex.com')),
            array(array('baiduspider'), array('baidu.com', 'baidu.jp')),
        );
    }

    /** Домени PTR для User-Agent, що називається ботом із правилом, або null. */
    function tyn_gate_bot_suffixes($ua)
    {
        if (!is_string($ua) || $ua === '') {
            return null;
        }
        $l = strtolower($ua);
        foreach (tyn_gate_bot_rules() as $rule) {
            foreach ($rule[0] as $needle) {
                if (strpos($l, $needle) !== false) {
                    return $rule[1];
                }
            }
        }
        return null;
    }

    /** Ім'я для PTR-запиту: 1.2.3.4 -> 4.3.2.1.in-addr.arpa; IPv6 -> nibble.ip6.arpa. Порожньо, якщо адреса некоректна. */
    function tyn_dns_arpa($ip)
    {
        $bin = is_string($ip) ? @inet_pton($ip) : false;
        if ($bin === false) {
            return '';
        }
        if (strlen($bin) === 4) {
            return implode('.', array_reverse(unpack('C4', $bin))) . '.in-addr.arpa';
        }
        return implode('.', array_reverse(str_split(bin2hex($bin)))) . '.ip6.arpa';
    }

    /** Читає ім'я з DNS-пакета (з вказівниками стиснення). Повертає рядок або null; $end: зсув за іменем у потоці. */
    function tyn_dns_name($p, $o, &$end)
    {
        $labels = array();
        $hops = 0;
        $end = null;
        $len = strlen($p);
        while (true) {
            if ($o >= $len) {
                return null;
            }
            $c = ord($p[$o]);
            if ($c === 0) {
                if ($end === null) {
                    $end = $o + 1;
                }
                break;
            }
            if (($c & 0xC0) === 0xC0) {
                if ($o + 1 >= $len) {
                    return null;
                }
                if ($end === null) {
                    $end = $o + 2;
                }
                if (++$hops > 20) {
                    return null;
                }
                $o = (($c & 0x3F) << 8) | ord($p[$o + 1]);
                continue;
            }
            if (($c & 0xC0) !== 0 || $o + 1 + $c > $len || count($labels) > 127) {
                return null;
            }
            $labels[] = substr($p, $o + 1, $c);
            $o += 1 + $c;
        }
        return implode('.', $labels);
    }

    /**
     * Розбір відповіді DNS (UDP). $qtype: 1 A, 12 PTR, 28 AAAA. Повертає масив відповідей (рядки; порожній для NXDOMAIN чи
     * відповіді без записів) або null (чужий id, обрізана, SERVFAIL тощо = «невідомо»).
     */
    function tyn_dns_parse($p, $id, $qtype)
    {
        if (!is_string($p) || strlen($p) < 12) {
            return null;
        }
        $h = unpack('nid/nflags/nqd/nan/nns/nar', substr($p, 0, 12));
        if ($h['id'] !== $id || !($h['flags'] & 0x8000) || ($h['flags'] & 0x0200)) {
            return null;
        }
        $rcode = $h['flags'] & 0xF;
        if ($rcode === 3) {
            return array();
        }
        if ($rcode !== 0) {
            return null;
        }
        $o = 12;
        for ($i = 0; $i < $h['qd']; $i++) {
            if (tyn_dns_name($p, $o, $end) === null) {
                return null;
            }
            $o = $end + 4;
        }
        $out = array();
        for ($i = 0; $i < $h['an']; $i++) {
            if (tyn_dns_name($p, $o, $end) === null || $end + 10 > strlen($p)) {
                return null;
            }
            $rr = unpack('ntype/nclass/Nttl/nlen', substr($p, $end, 10));
            $rd = $end + 10;
            $o = $rd + $rr['len'];
            if ($o > strlen($p)) {
                return null;
            }
            if ($rr['type'] !== $qtype || $rr['class'] !== 1) {
                continue;
            }
            if ($qtype === 12) {
                $n = tyn_dns_name($p, $rd, $e2);
                if ($n === null) {
                    return null;
                }
                $out[] = strtolower($n);
            } elseif ($qtype === 1 && $rr['len'] === 4) {
                $out[] = implode('.', unpack('C4', substr($p, $rd, 4)));
            } elseif ($qtype === 28 && $rr['len'] === 16) {
                $out[] = inet_ntop(substr($p, $rd, 16));
            }
        }
        return $out;
    }

    /** Системні DNS-сервери з /etc/resolv.conf (до двох); порожній масив, якщо файла немає чи його не можна читати. */
    function tyn_dns_nameservers()
    {
        $lines = @file('/etc/resolv.conf', FILE_IGNORE_NEW_LINES);
        $out = array();
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (preg_match('/^\s*nameserver\s+(\S+)/', $line, $m) && filter_var($m[1], FILTER_VALIDATE_IP) !== false) {
                    $out[] = $m[1];
                }
            }
        }
        return array_slice($out, 0, 2);
    }

    /**
     * Один DNS-запит по UDP із жорстким тайм-аутом (не залежить від налаштувань резолвера PHP). $type: A, AAAA, PTR.
     * $servers: "ip" чи "ip:порт"; $deadline: microtime(true), після якого не чекаємо. Повертає масив чи null (див. tyn_dns_parse).
     */
    function tyn_dns_query($name, $type, array $servers, $deadline)
    {
        $codes = array('A' => 1, 'PTR' => 12, 'AAAA' => 28);
        if (!isset($codes[$type]) || !is_string($name) || $name === '') {
            return null;
        }
        $id = function_exists('random_int') ? random_int(0, 65535) : mt_rand(0, 65535);
        $pkt = pack('n6', $id, 0x0100, 1, 0, 0, 0);
        foreach (explode('.', rtrim($name, '.')) as $label) {
            if ($label === '' || strlen($label) > 63) {
                return null;
            }
            $pkt .= chr(strlen($label)) . $label;
        }
        $pkt .= "\0" . pack('n2', $codes[$type], 1);
        foreach ($servers as $ns) {
            $left = $deadline - microtime(true);
            if ($left < 0.05) {
                break;
            }
            $t = min(TYN_DNS_TIMEOUT, $left);
            if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $ns, $m)) {
                $addr = 'udp://' . $m[1] . ':' . $m[2];
            } elseif (strpos($ns, ':') !== false) {
                $addr = 'udp://[' . $ns . ']:53';
            } else {
                $addr = 'udp://' . $ns . ':53';
            }
            $fp = @stream_socket_client($addr, $en, $es, $t);
            if (!$fp) {
                continue;
            }
            @stream_set_timeout($fp, (int) $t, (int) (($t - floor($t)) * 1000000));
            @fwrite($fp, $pkt);
            $resp = @fread($fp, 1500);
            fclose($fp);
            if (is_string($resp) && $resp !== '') {
                $r = tyn_dns_parse($resp, $id, $codes[$type]);
                if ($r !== null) {
                    return $r;
                }
            }
        }
        return null;
    }

    /** Типовий резолвер: власний UDP-клієнт із тайм-аутом, а без доступу до resolv.conf звичайні функції PHP (тайм-аут не контролюється). */
    function tyn_dns_default_resolver($servers)
    {
        if (!$servers) {
            $servers = tyn_dns_nameservers();
        }
        return function ($type, $name, $deadline, $ip) use ($servers) {
            if ($servers) {
                return tyn_dns_query($name, $type, $servers, $deadline);
            }
            if ($type === 'PTR') {
                $h = @gethostbyaddr($ip);
                return (is_string($h) && $h !== $ip) ? array(strtolower($h)) : null;
            }
            if ($type === 'A') {
                $r = @gethostbynamel($name);
                return is_array($r) ? $r : null;
            }
            if (function_exists('dns_get_record')) {
                $r = @dns_get_record($name, DNS_AAAA);
                $out = array();
                if (is_array($r)) {
                    foreach ($r as $rec) {
                        if (isset($rec['ipv6'])) {
                            $out[] = $rec['ipv6'];
                        }
                    }
                    return $out;
                }
            }
            return null;
        };
    }

    /** Перевірка PTR: ok / fake / unknown (див. tyn_gate_bot_verified). $resolver: function ($type, $name, $deadline, $ip). */
    function tyn_gate_bot_check($ip, array $suffixes, $resolver)
    {
        $deadline = microtime(true) + TYN_DNS_BUDGET;
        $ptr = call_user_func($resolver, 'PTR', tyn_dns_arpa($ip), $deadline, $ip);
        if ($ptr === null || !is_array($ptr)) {
            return 'unknown';
        }
        $want = @inet_pton($ip);
        $type = strlen($want) === 4 ? 'A' : 'AAAA';
        $unknown = false;
        foreach (array_slice($ptr, 0, 3) as $host) {
            $h = rtrim(strtolower((string) $host), '.');
            $okHost = false;
            foreach ($suffixes as $s) {
                $s = '.' . $s;
                if (strlen($h) > strlen($s) && substr($h, -strlen($s)) === $s) {
                    $okHost = true;
                }
            }
            if (!$okHost) {
                continue;
            }
            $fw = call_user_func($resolver, $type, $h, $deadline, $ip);
            if ($fw === null || !is_array($fw)) {
                $unknown = true;
                continue;
            }
            foreach ($fw as $addr) {
                if (@inet_pton($addr) === $want) {
                    return 'ok';
                }
            }
        }
        return $unknown ? 'unknown' : 'fake';
    }

    /** Кешований результат для IP: статус чи null. Кеш: один файл у $dir, рядки "ip\tстатус\tдо", flock. */
    function tyn_gate_bot_cache_get($dir, $ip, $now)
    {
        $f = rtrim($dir, '/\\') . '/tyn-bot.cache';
        $fp = @fopen($f, 'rb');
        if (!$fp) {
            return null;
        }
        $res = null;
        if (@flock($fp, LOCK_SH)) {
            $data = (string) @stream_get_contents($fp);
            flock($fp, LOCK_UN);
            foreach (explode("\n", $data) as $line) {
                $c = explode("\t", $line);
                if (count($c) === 3 && $c[0] === $ip && (int) $c[2] > $now && in_array($c[1], array('ok', 'fake', 'unknown'), true)) {
                    $res = $c[1];
                }
            }
        }
        fclose($fp);
        return $res;
    }

    /** Записує результат у кеш; прострочене прибирається, при переповненні (TYN_BOT_CACHE_MAX) лишається нова половина. */
    function tyn_gate_bot_cache_put($dir, $ip, $status, $until, $now)
    {
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            return;
        }
        $f = rtrim($dir, '/\\') . '/tyn-bot.cache';
        if (!is_file(rtrim($dir, '/\\') . '/.htaccess')) {
            @file_put_contents(rtrim($dir, '/\\') . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
        $old = umask(0077);
        $fp = @fopen($f, 'c+b');
        umask($old);
        if (!$fp) {
            return;
        }
        if (@flock($fp, LOCK_EX)) {
            $keep = array();
            foreach (explode("\n", (string) stream_get_contents($fp)) as $line) {
                $c = explode("\t", $line);
                if (count($c) === 3 && $c[0] !== $ip && (int) $c[2] > $now) {
                    $keep[] = $line;
                }
            }
            $keep[] = $ip . "\t" . $status . "\t" . (int) $until;
            $out = implode("\n", $keep) . "\n";
            if (strlen($out) > TYN_BOT_CACHE_MAX) {
                $keep = array_slice($keep, (int) (count($keep) / 2));
                $out = implode("\n", $keep) . "\n";
            }
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $out);
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /**
     * Чи справжній бот, за яким відвідувач себе видає. Повертає:
     *   none     UA не називає бота з правилом PTR (перевіряти нічого: решта ботів лишається за UA);
     *   ok       PTR адреси лежить у домені пошуковика й пряма адреса імені збігається з IP;
     *   fake     PTR немає, чужий домен чи пряма адреса не збігається (підробка);
     *   unknown  адреси немає/приватна, DNS не відповів чи збій (fail-open: викликач пропускає як бота).
     * $cacheDir: тека кешу (порожньо = без кешу, кожен запит іде в DNS). Кеш: ok 24 год, fake 1 год, unknown 5 хв, тож на один
     * IP не більше одного DNS-запиту за цей час. $opts: now, resolver (callable для тестів), nameservers (масив "ip[:порт]").
     * Тайм-аут: власний UDP-клієнт, 1 с на запит і 2 с на всю перевірку; без /etc/resolv.conf чи сокетів PHP робить
     * gethostbyaddr/gethostbynamel, у яких тайм-ауту налаштувати не можна (тільки системний).
     */
    function tyn_gate_bot_verified($ip, $ua, $cacheDir = '', $opts = array())
    {
        try {
            $suffixes = tyn_gate_bot_suffixes($ua);
            if ($suffixes === null) {
                return 'none';
            }
            if (!is_string($ip) || tyn_public_ip($ip) === '') {
                return 'unknown';
            }
            $opts = is_array($opts) ? $opts : array();
            $now = isset($opts['now']) ? (int) $opts['now'] : time();
            $useCache = is_string($cacheDir) && $cacheDir !== '';
            if ($useCache) {
                $c = tyn_gate_bot_cache_get($cacheDir, $ip, $now);
                if ($c !== null) {
                    return $c;
                }
            }
            $resolver = isset($opts['resolver']) && is_callable($opts['resolver']) ? $opts['resolver']
                : tyn_dns_default_resolver(isset($opts['nameservers']) && is_array($opts['nameservers']) ? $opts['nameservers'] : array());
            $status = tyn_gate_bot_check($ip, $suffixes, $resolver);
            if ($useCache) {
                $ttl = $status === 'ok' ? TYN_BOT_TTL_OK : ($status === 'fake' ? TYN_BOT_TTL_FAKE : TYN_BOT_TTL_UNKNOWN);
                tyn_gate_bot_cache_put($cacheDir, $ip, $status, $now + $ttl, $now);
            }
            return $status;
        } catch (Exception $e) {
            return 'unknown';
        } catch (Throwable $e) {
            return 'unknown';
        }
    }

    /**
     * Клас User-Agent: empty (порожній), script (curl, python, Go, headless тощо),
     * bot (самооголошений бот), browser (звичайний браузер), other (решта).
     */
    function tyn_gate_ua_class($ua)
    {
        if (!is_string($ua) || trim($ua) === '') {
            return 'empty';
        }
        $l = strtolower($ua);
        $script = array(
            'curl/', 'wget', 'python', 'go-http-client', 'java/', 'libwww', 'okhttp', 'httpclient', 'http_request', 'node-fetch',
            'axios', 'got (', 'undici', 'scrapy', 'phantomjs', 'headless', 'selenium', 'puppeteer', 'playwright', 'httpie',
            'postmanruntime', 'insomnia', 'aiohttp', 'mechanize', 'php/', 'guzzle', 'perl', 'ruby', 'libcurl',
        );
        foreach ($script as $needle) {
            if (strpos($l, $needle) !== false) {
                return 'script';
            }
        }
        if (preg_match('/bot|crawl|spider|slurp|fetcher|scanner|monitor/', $l)) {
            return 'bot';
        }
        if (strpos($l, 'mozilla/') === 0 && preg_match('/chrome|safari|firefox|edg|opr\/|opera|msie|trident|webkit|gecko/', $l)) {
            return 'browser';
        }
        return 'other';
    }

    /** Типові винятки маршрутів (шаблони з * і ?, нижній регістр), їх не можна вимкнути. */
    function tyn_gate_default_routes()
    {
        return array(
            'api/*', 'extension/payment/*', 'payment/*', 'extension/shipping/*', 'shipping/*',
            'extension/feed/*', 'feed/*', 'extension/total/*', 'checkout/*', 'extension/module/tyn*',
            '*callback*', '*notify*', '*webhook*', '*ipn*', '*sitemap*', '*yml*', '*export*', '*feed*',
            'common/cart*', 'revolution/revpopupcart*', 'tool/*',
            // OpenCart 4: маршрути розширень мають вигляд extension/<код>/<тип>/<назва>.
            'extension/tyn/*', 'extension/*/payment/*', 'extension/*/shipping/*', 'extension/*/total/*', 'extension/*/feed/*',
            'cron/*', 'extension/*/cron/*',
        );
    }

    /** Чи маршрут (або шлях) збігається з одним із шаблонів. */
    function tyn_gate_route_match($route, $patterns)
    {
        $route = strtolower((string) $route);
        foreach ($patterns as $p) {
            if (is_string($p) && $p !== '' && function_exists('fnmatch') && fnmatch(strtolower($p), $route)) {
                return true;
            }
        }
        return false;
    }

    /** Ключ підпису позначки: похідний від секрету сайту (сам секрет у підпис не йде). */
    function tyn_gate_key($secret)
    {
        return hash_hmac('sha256', 'tyn-gate-v1', (string) $secret);
    }

    /** Скорочена мережа клієнта для прив'язки позначки: IPv4 /24, IPv6 /48, інакше порожньо. */
    function tyn_gate_net($ip)
    {
        $net = is_string($ip) ? tyn_traffic_net($ip) : '';
        return is_string($net) ? $net : '';
    }

    /**
     * Значення cookie позначки: "1.<кінець дії>.<мережа або 0>.<підпис>". Усередині лише час і
     * (необов'язково) HMAC скороченої мережі; жодного ідентифікатора людини.
     * $ttl: секунди дії; $bindNet: скорочена мережа (рядок) або '' без прив'язки.
     */
    function tyn_gate_mark_make($secret, $ttl, $bindNet = '', $now = null)
    {
        $now = $now === null ? time() : (int) $now;
        $exp = $now + max(1, (int) $ttl);
        $key = tyn_gate_key($secret);
        $net = ($bindNet === '' || $bindNet === null) ? '0' : substr(hash_hmac('sha256', 'net|' . $bindNet, $key), 0, 12);
        $sig = substr(hash_hmac('sha256', '1|' . $exp . '|' . $net, $key), 0, 32);
        return '1.' . $exp . '.' . $net . '.' . $sig;
    }

    /** Чи позначка справжня й чинна. $bindNet: поточна скорочена мережа (якщо в позначці є прив'язка). */
    function tyn_gate_mark_valid($secret, $value, $bindNet = '', $now = null)
    {
        if (!is_string($value) || strlen($value) > 120 || !preg_match('/^1\.(\d{1,12})\.([0-9a-f]{1,12})\.([0-9a-f]{32})$/', $value, $m)) {
            return false;
        }
        $now = $now === null ? time() : (int) $now;
        $key = tyn_gate_key($secret);
        $sig = substr(hash_hmac('sha256', '1|' . $m[1] . '|' . $m[2], $key), 0, 32);
        if (!hash_equals($sig, $m[3])) {
            return false;
        }
        if ((int) $m[1] < $now) {
            return false;
        }
        if ($m[2] !== '0') {
            if ($bindNet === '' || $bindNet === null) {
                return false;
            }
            $net = substr(hash_hmac('sha256', 'net|' . $bindNet, $key), 0, 12);
            if (!hash_equals($net, $m[2])) {
                return false;
            }
        }
        return true;
    }

    /** Заголовок Set-Cookie позначки: HttpOnly, SameSite=Lax, Secure на https, без Domain. */
    function tyn_gate_cookie_header($value, $ttl, $secure, $path = '/', $now = null)
    {
        $now = $now === null ? time() : (int) $now;
        $path = (is_string($path) && $path !== '' && $path[0] === '/' && !preg_match('/[;\s,]/', $path)) ? $path : '/';
        return 'Set-Cookie: ' . TYN_GATE_COOKIE . '=' . $value
            . '; Expires=' . gmdate('D, d M Y H:i:s', $now + (int) $ttl) . ' GMT'
            . '; Max-Age=' . (int) $ttl
            . '; Path=' . $path . '; HttpOnly; SameSite=Lax' . ($secure ? '; Secure' : '');
    }

    /**
     * Безпечна адреса повернення: лише відносний шлях цього ж сайту ("/…", не "//…", без схеми,
     * без зворотної скісної, керівних символів і CR/LF). Інакше "/" (або $fallback).
     */
    function tyn_gate_safe_return($url, $fallback = '/')
    {
        if (!is_string($url) || $url === '' || strlen($url) > 2000) {
            return $fallback;
        }
        if ($url[0] !== '/' || (isset($url[1]) && ($url[1] === '/' || $url[1] === '\\'))) {
            return $fallback;
        }
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $url) || preg_match('/[^\x21-\x7e]/', $url)) {
            return $fallback;
        }
        // Після розкодування теж не повинно починатися з "//" чи "/\" (деякі клієнти декодують).
        $dec = rawurldecode($url);
        if (preg_match('#^/[/\\\\]#', $dec) || preg_match('/[\x00-\x1f\\\\]/', $dec)) {
            return $fallback;
        }
        $parts = @parse_url($url);
        if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
            return $fallback;
        }
        return $url;
    }

    /**
     * Рішення, чи показувати заслін. $server: $_SERVER; $cookies: $_COOKIE.
     * $opts: mode ('all'|'suspicious'), secret, route (головний маршрут), logged_in (bool),
     *        bots (додаткові підрядки UA), routes (додаткові шаблони маршрутів), paths (додаткові шаблони шляху,
     *        що починаються з "/"), bind (bool), ip (адреса клієнта), now, cli (bool, для тестів).
     *        mode 'observe': нікого не зупиняє (show завжди false); якщо відвідувача було б зупинено, повертає
     *        reason 'observe', would_all => true і would_suspicious => ознака (порожньо, якщо в режимі suspicious
     *        його не зупинили б).
     *        bot_dir (тека кешу: вмикає перевірку справжності названих ботів за PTR, див. tyn_gate_bot_verified), bot_ip
     *        (справжня адреса клієнта для неї; типово ip), bot_resolver, bot_nameservers (для тестів).
     * Повертає array('show' => bool, 'reason' => код). Коди «не показувати»: off, cli, method, ajax, not_html,
     * bot, bot_unverified (бот без підтвердження, бо DNS не відповів), route, logged_in, mark, no_secret;
     * «показувати»: all, suspicious, fake_bot (назвався ботом, PTR не підтвердив).
     */
    function tyn_gate_decide(array $server, array $cookies, array $opts)
    {
        try {
            $mode = isset($opts['mode']) ? $opts['mode'] : '';
            if ($mode !== 'all' && $mode !== 'suspicious' && $mode !== 'observe') {
                return array('show' => false, 'reason' => 'off');
            }
            $cli = isset($opts['cli']) ? (bool) $opts['cli'] : (PHP_SAPI === 'cli');
            if ($cli) {
                return array('show' => false, 'reason' => 'cli');
            }
            $secret = isset($opts['secret']) && is_string($opts['secret']) ? $opts['secret'] : '';
            if ($secret === '') {
                return array('show' => false, 'reason' => 'no_secret');
            }
            $method = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? strtoupper($server['REQUEST_METHOD']) : '';
            if ($method !== 'GET') {
                return array('show' => false, 'reason' => 'method');
            }
            $h = function ($name) use ($server) {
                $k = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
                return (isset($server[$k]) && is_string($server[$k])) ? $server[$k] : '';
            };
            // Фонові запити: ajax, не навігація документа, клієнт не хоче HTML.
            if (strtolower($h('X-Requested-With')) === 'xmlhttprequest') {
                return array('show' => false, 'reason' => 'ajax');
            }
            $dest = strtolower($h('Sec-Fetch-Dest'));
            if ($dest !== '' && $dest !== 'document') {
                return array('show' => false, 'reason' => 'ajax');
            }
            $accept = strtolower($h('Accept'));
            if ($accept !== '' && strpos($accept, 'text/html') === false && strpos($accept, '*/*') === false) {
                return array('show' => false, 'reason' => 'not_html');
            }
            $ua = $h('User-Agent');
            $extraBots = isset($opts['bots']) && is_array($opts['bots']) ? $opts['bots'] : array();
            $fake = false;
            if (tyn_gate_is_allowed_bot($ua, $extraBots)) {
                $verify = 'none';
                if (!empty($opts['bot_dir']) && is_string($opts['bot_dir'])) {
                    $botIp = isset($opts['bot_ip']) ? $opts['bot_ip'] : (isset($opts['ip']) ? $opts['ip'] : '');
                    $verify = tyn_gate_bot_verified($botIp, $ua, $opts['bot_dir'], array(
                        'now' => isset($opts['now']) ? $opts['now'] : null,
                        'resolver' => isset($opts['bot_resolver']) ? $opts['bot_resolver'] : null,
                        'nameservers' => isset($opts['bot_nameservers']) ? $opts['bot_nameservers'] : array(),
                    ));
                }
                if ($verify === 'fake') {
                    $fake = true; // назвався ботом, але PTR не підтвердив: пільги бота немає
                } else {
                    return array('show' => false, 'reason' => $verify === 'unknown' ? 'bot_unverified' : 'bot');
                }
            }
            // Маршрут і шлях (карти сайту, вивантаження).
            $route = isset($opts['route']) && is_string($opts['route']) ? $opts['route'] : '';
            $patterns = array_merge(tyn_gate_default_routes(), isset($opts['routes']) && is_array($opts['routes']) ? $opts['routes'] : array());
            if ($route !== '' && tyn_gate_route_match($route, $patterns)) {
                return array('show' => false, 'reason' => 'route');
            }
            $uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
            $path = strtolower((string) substr($uri, 0, strcspn($uri, '?#')));
            if (preg_match('#\.(xml|yml|json|csv|txt|rss|atom|gz|zip|js|css|map|webmanifest)$#', $path) || strpos($path, 'sitemap') !== false) {
                return array('show' => false, 'reason' => 'route');
            }
            if (isset($opts['paths']) && is_array($opts['paths']) && tyn_gate_route_match($path, $opts['paths'])) {
                return array('show' => false, 'reason' => 'route');
            }
            if (!empty($opts['logged_in'])) {
                return array('show' => false, 'reason' => 'logged_in');
            }
            // Чинна позначка проходу.
            $net = !empty($opts['bind']) ? tyn_gate_net(isset($opts['ip']) ? $opts['ip'] : '') : '';
            $now = isset($opts['now']) ? (int) $opts['now'] : null;
            if (isset($cookies[TYN_GATE_COOKIE]) && tyn_gate_mark_valid($secret, $cookies[TYN_GATE_COOKIE], $net, $now)) {
                return array('show' => false, 'reason' => 'mark');
            }
            if ($mode === 'all') {
                return array('show' => true, 'reason' => $fake ? 'fake_bot' : 'all');
            }
            // suspicious (і observe): ознаки не звичайного браузера.
            $class = tyn_gate_ua_class($ua);
            $why = '';
            if ($fake) {
                $why = 'fake_bot';
            } elseif ($class !== 'browser') {
                $why = 'ua_' . $class;
            } elseif (trim($h('Accept-Language')) === '') {
                $why = 'no_language';
            } elseif (preg_match('/headless/i', $h('Sec-CH-UA')) || $h('X-Selenium') !== '' || $h('X-Puppeteer') !== '' || $h('X-Automation') !== '') {
                $why = 'automation';
            } elseif ($accept === '') {
                $why = 'no_accept';
            }
            if ($mode === 'observe') {
                return array('show' => false, 'reason' => 'observe', 'would_all' => true, 'would_suspicious' => $why);
            }
            if ($why !== '') {
                return array('show' => true, 'reason' => $why === 'fake_bot' ? 'fake_bot' : 'suspicious:' . $why);
            }
            return array('show' => false, 'reason' => 'browser');
        } catch (Exception $e) {
            return array('show' => false, 'reason' => 'error');
        } catch (Throwable $e) {
            return array('show' => false, 'reason' => 'error');
        }
    }

    /**
     * Режим observe: дописує в локальний файл дня один рядок "<година UTC>\t<all|ознака>" для кожного запиту,
     * який було б зупинено ("all" лічить усіх, ознака лише тих, кого зупинив би режим suspicious).
     * Без адрес, агентів і шляхів; мережі й сервісу не торкається. Файли старші за 14 діб прибираються.
     * $dir: тека (створюється сама). Повертає true, якщо записано.
     */
    function tyn_gate_observe_record($dir, array $decision, $now = null)
    {
        try {
            if (empty($decision['would_all']) || !is_string($dir) || $dir === '') {
                return false;
            }
            $now = $now === null ? time() : (int) $now;
            $dir = rtrim($dir, '/') . '/';
            if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
                return false;
            }
            $why = isset($decision['would_suspicious']) && is_string($decision['would_suspicious']) ? $decision['would_suspicious'] : '';
            $why = preg_replace('/[^a-z0-9_]/', '', strtolower($why));
            $file = $dir . 'obs-' . gmdate('Ymd', $now) . '.log';
            $line = gmdate('H', $now) . "\tall\n" . ($why !== '' ? gmdate('H', $now) . "\t" . $why . "\n" : '');
            $ok = @file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
            if ($ok && mt_rand(1, 200) === 1) {
                foreach ((array) glob($dir . 'obs-*.log') as $old) {
                    if (is_file($old) && @filemtime($old) < $now - 14 * 86400) {
                        @unlink($old);
                    }
                }
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Підсумок observe за останні $days діб: array('YYYYMMDD' => array(код => кількість)). Лише читання. */
    function tyn_gate_observe_stats($dir, $days = 7, $now = null)
    {
        $out = array();
        try {
            $now = $now === null ? time() : (int) $now;
            $dir = rtrim((string) $dir, '/') . '/';
            for ($i = 0; $i < max(1, min(14, (int) $days)); $i++) {
                $d = gmdate('Ymd', $now - $i * 86400);
                $f = $dir . 'obs-' . $d . '.log';
                if (!is_file($f)) {
                    continue;
                }
                $row = array();
                foreach ((array) @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
                    $parts = explode("\t", $l);
                    if (count($parts) === 2 && preg_match('/^[a-z0-9_]{1,40}$/', $parts[1])) {
                        $row[$parts[1]] = (isset($row[$parts[1]]) ? $row[$parts[1]] : 0) + 1;
                    }
                }
                if ($row) {
                    $out[$d] = $row;
                }
            }
        } catch (Exception $e) {
        } catch (Throwable $e) {
        }
        return $out;
    }

    /** Тексти сторінки-заслону за мовою (uk, ru, en; інакше uk). */
    function tyn_gate_texts($lang)
    {
        $lang = strtolower(substr((string) $lang, 0, 2));
        $all = array(
            'uk' => array(
                'title' => 'Перевірка відвідувача',
                'text' => 'Щоб продовжити, пройдіть коротку перевірку. Це потрібно один раз за відвідування.',
                'button' => 'Продовжити',
                'noscript' => 'Для перевірки потрібен JavaScript. Увімкніть його в браузері й оновіть сторінку.',
                'retry' => 'Перевірку не завершено. Спробуйте ще раз.',
                'cookies' => 'Схоже, браузер не зберігає cookie. Увімкніть їх і спробуйте ще раз.',
            ),
            'ru' => array(
                'title' => 'Проверка посетителя',
                'text' => 'Чтобы продолжить, пройдите короткую проверку. Это нужно один раз за посещение.',
                'button' => 'Продолжить',
                'noscript' => 'Для проверки нужен JavaScript. Включите его в браузере и обновите страницу.',
                'retry' => 'Проверка не завершена. Попробуйте ещё раз.',
                'cookies' => 'Похоже, браузер не сохраняет cookie. Включите их и попробуйте ещё раз.',
            ),
            'en' => array(
                'title' => 'Visitor check',
                'text' => 'Please complete a short check to continue. It is needed once per visit.',
                'button' => 'Continue',
                'noscript' => 'JavaScript is required for the check. Enable it in your browser and reload the page.',
                'retry' => 'The check was not completed. Please try again.',
                'cookies' => 'Your browser does not seem to keep cookies. Enable them and try again.',
            ),
        );
        return isset($all[$lang]) ? $all[$lang] : $all['uk'];
    }

    /**
     * Сторінка-заслін: проста розмітка без теми магазину. $o: site_key, base_url, action (відносна адреса
     * маршруту проходу), return (безпечна відносна адреса), lang, note ('', 'retry', 'cookies'), auto (bool: автовідправлення).
     * Секрет сюди не потрапляє. Повертає порожній рядок, якщо віджет зібрати неможливо.
     */
    function tyn_gate_page(array $o)
    {
        $widget = tyn_widget(isset($o['site_key']) ? $o['site_key'] : '', isset($o['base_url']) ? $o['base_url'] : '', 'other');
        if ($widget === '') {
            return '';
        }
        $t = tyn_gate_texts(isset($o['lang']) ? $o['lang'] : 'uk');
        $lang = in_array(strtolower(substr((string) (isset($o['lang']) ? $o['lang'] : ''), 0, 2)), array('uk', 'ru', 'en'), true)
            ? strtolower(substr((string) $o['lang'], 0, 2)) : 'uk';
        $note = '';
        if (isset($o['note']) && isset($t[$o['note']]) && in_array($o['note'], array('retry', 'cookies'), true)) {
            $note = '<p class="n">' . tyn_esc($t[$o['note']]) . '</p>';
        }
        $auto = !empty($o['auto']) ? '1' : '0';
        return '<!DOCTYPE html><html lang="' . $lang . '"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<meta name="robots" content="noindex,nofollow">'
            . '<title>' . tyn_esc($t['title']) . '</title>'
            . '<style>body{margin:0;font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f5f5f7;color:#222}'
            . 'main{max-width:420px;margin:12vh auto;padding:24px;background:#fff;border-radius:8px;box-shadow:0 1px 6px rgba(0,0,0,.12);text-align:center}'
            . 'h1{font-size:20px;margin:0 0 12px}.n{color:#b00020}button{margin-top:16px;padding:10px 24px;font-size:16px;cursor:pointer}'
            . '.tyn-hp{position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden}</style></head><body><main>'
            . '<h1>' . tyn_esc($t['title']) . '</h1><p>' . tyn_esc($t['text']) . '</p>' . $note
            . '<form id="tyn-gate" method="post" action="' . tyn_esc(isset($o['action']) ? $o['action'] : '') . '">'
            . '<input type="hidden" name="return" value="' . tyn_esc(isset($o['return']) ? $o['return'] : '/') . '">'
            . $widget
            . '<button type="submit">' . tyn_esc($t['button']) . '</button></form>'
            . '<noscript><p class="n">' . tyn_esc($t['noscript']) . '</p></noscript>'
            . '<script>(function(){var f=document.getElementById("tyn-gate");if(!f||"' . $auto . '"!=="1"){return}'
            . 'var s=false,go=function(){if(s){return}s=true;f.submit()};'
            . 'var b=f.querySelector("[data-tyn]");if(b){b.addEventListener("tyn:token",go)}'
            . 'var n=0,i=setInterval(function(){var v=f.querySelector("input[name=tyn_token]");n++;'
            . 'if(v&&v.value){clearInterval(i);go()}else if(n>60){clearInterval(i)}},500)})();</script>'
            . '</body></html>';
    }
    // ---------------------------------------------------------------------
    // Готовий заслін для будь-якого PHP-сайту (php/agent.php, плагін WordPress): чиста
    // функція tyn_gate_process() вирішує, що віддати; tyn_gate_send() виводить і завершує запит.
    // ---------------------------------------------------------------------

    /** Параметр адреси, яким форма заслону надсилається на той самий сайт. */
    define('TYN_GATE_PASS_PARAM', '__tyn_gate');

    /** Типові винятки шляхів для довільного сайту (шаблони * і ?, нижній регістр). Вимкнути не можна. */
    function tyn_gate_default_paths()
    {
        return array(
            '/robots.txt', '/favicon.ico', '/sitemap*', '*sitemap*.xml*', '/feed', '/feed/*', '*/feed', '*/feed/*', '*/feed.*', '/rss*', '/atom*',
            '/.well-known/*', '/wp-cron.php', '/wp-json', '/wp-json/*', '/xmlrpc.php', '/wp-admin', '/wp-admin/*', '/wp-login.php',
            '/wp-content/*', '/wp-includes/*', '/wc-api/*', '/admin', '/admin/*', '/administrator', '/administrator/*',
            '/api', '/api/*', '/webhook*', '/webhooks/*', '*/webhook*', '*callback*', '*/ipn*', '*notify*',
        );
    }

    /** Чи з'єднання захищене (прямо або за проксі, що передає схему). */
    function tyn_gate_is_secure(array $server)
    {
        if (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off') {
            return true;
        }
        if (isset($server['SERVER_PORT']) && (string) $server['SERVER_PORT'] === '443') {
            return true;
        }
        if (isset($server['HTTP_X_FORWARDED_PROTO']) && is_string($server['HTTP_X_FORWARDED_PROTO'])) {
            $p = explode(',', $server['HTTP_X_FORWARDED_PROTO']);
            return strtolower(trim($p[0])) === 'https';
        }
        return false;
    }

    /** Чи шлях збігається з регулярними виразами (без роздільників; хибний вираз пропускається). */
    function tyn_gate_regex_match($path, $patterns)
    {
        if (!is_array($patterns)) {
            return false;
        }
        foreach ($patterns as $re) {
            if (!is_string($re) || $re === '' || strlen($re) > 200) {
                continue;
            }
            $r = @preg_match('~' . str_replace('~', '\~', $re) . '~i', $path);
            if ($r === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Що віддати на запит. $cfg: mode (off|observe|suspicious|all), site_key, secret, base_url, ttl_hours (1–720, типово 12),
     * bind (bool), bots (масив), paths (масив шаблонів шляхів), regex (масив регулярних виразів), proxy_header, trusted_proxies (масив),
     * buffer_dir (для observe), lang, logged_in (bool), cookie_path, now,
     * track (bool: слати події gate на /v1/track після відповіді). $server/$cookies/$post: суперглобальні масиви.
     * $check: необов'язкова функція перевірки токена (для тестів), типово tyn_check у м'якому режимі.
     * Повертає array('type' => 'none'|'page'|'redirect', 'status', 'headers' => масив рядків, 'body', 'reason').
     * 'none' означає «віддати сайт, як без заслону». Будь-який збій дає 'none'.
     */
    function tyn_gate_process(array $cfg, array $server, array $cookies, array $post, $check = null)
    {
        $none = array('type' => 'none', 'status' => 200, 'headers' => array(), 'body' => '', 'reason' => '');
        try {
            $mode = isset($cfg['mode']) ? $cfg['mode'] : 'off';
            if (!in_array($mode, array('observe', 'suspicious', 'all'), true)) {
                $none['reason'] = 'off';
                return $none;
            }
            $secret = isset($cfg['secret']) && is_string($cfg['secret']) ? $cfg['secret'] : '';
            $base = isset($cfg['base_url']) ? $cfg['base_url'] : '';
            $siteKey = isset($cfg['site_key']) ? $cfg['site_key'] : '';
            if ($secret === '' || tyn_normalize_base($base) === '' || !is_string($siteKey) || $siteKey === '') {
                $none['reason'] = 'no_config';
                return $none;
            }
            $ttl = isset($cfg['ttl_hours']) ? (int) $cfg['ttl_hours'] : 12;
            $ttl = (($ttl < 1 || $ttl > 720) ? 12 : $ttl) * 3600;
            $bind = !empty($cfg['bind']);
            $now = isset($cfg['now']) ? (int) $cfg['now'] : time();
            $ip = tyn_traffic_client_ip($server, array(
                'proxy_header' => isset($cfg['proxy_header']) ? $cfg['proxy_header'] : '',
                'trusted_proxies' => isset($cfg['trusted_proxies']) && is_array($cfg['trusted_proxies']) ? $cfg['trusted_proxies'] : array(),
            ));
            $secure = tyn_gate_is_secure($server);
            $cookiePath = isset($cfg['cookie_path']) ? $cfg['cookie_path'] : '/';
            $uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
            $cut = strcspn($uri, '?#');
            $path = (string) substr($uri, 0, $cut);
            $query = (string) substr($uri, $cut);
            $method = isset($server['REQUEST_METHOD']) && is_string($server['REQUEST_METHOD']) ? strtoupper($server['REQUEST_METHOD']) : '';
            $page = function ($return, $note, $auto) use ($cfg, $path) {
                $pageHtml = tyn_gate_page(array(
                    'site_key' => $cfg['site_key'], 'base_url' => $cfg['base_url'],
                    'action' => ($path === '' ? '/' : $path) . '?' . TYN_GATE_PASS_PARAM . '=pass',
                    'return' => $return, 'lang' => isset($cfg['lang']) ? $cfg['lang'] : 'uk', 'note' => $note, 'auto' => $auto,
                ));
                return $pageHtml === '' ? false : array(
                    'type' => 'page', 'status' => 200, 'reason' => 'gate', 'body' => $pageHtml,
                    'headers' => array(
                        'Content-Type: text/html; charset=utf-8',
                        'Cache-Control: no-store, private, max-age=0',
                        'Pragma: no-cache',
                        'X-Robots-Tag: noindex, nofollow',
                    ),
                );
            };

            // Події gate для /v1/track (лише коли cfg['track'] увімкнено, лише рішення, відмінні від «пропустити»): без шляху; через файловий буфер у cfg['track_dir'].
            $track = function ($decision, $reason) use ($cfg, $server, $secret, $siteKey, $base) {
                if (empty($cfg['track'])) {
                    return;
                }
                tyn_track_add('gate', array('decision' => $decision, 'reason' => tyn_gate_track_reason($reason)));
                tyn_track_flush(array(
                    'key' => $siteKey, 'secret' => $secret, 'base_url' => $base, 'server' => $server,
                    'proxy_header' => isset($cfg['proxy_header']) ? $cfg['proxy_header'] : '',
                    'trusted_proxies' => isset($cfg['trusted_proxies']) && is_array($cfg['trusted_proxies']) ? $cfg['trusted_proxies'] : array(),
                    'buffer_dir' => isset($cfg['track_dir']) ? $cfg['track_dir'] : '',
                    'interval' => isset($cfg['track_interval']) ? $cfg['track_interval'] : TYN_TRACK_INTERVAL,
                    'uid' => isset($cfg['track_uid']) ? $cfg['track_uid'] : '',
                ));
            };

            // Прохід: форма заслону (POST на ту саму адресу з параметром).
            if ($method === 'POST' && isset($server['QUERY_STRING']) && is_string($server['QUERY_STRING'])
                && strpos('&' . $server['QUERY_STRING'], '&' . TYN_GATE_PASS_PARAM . '=pass') !== false) {
                if ($mode === 'observe') {
                    $none['reason'] = 'observe';
                    return $none;
                }
                $return = tyn_gate_safe_return(isset($post['return']) && is_string($post['return']) ? $post['return'] : '/');
                $token = isset($post['tyn_token']) && is_string($post['tyn_token']) ? $post['tyn_token'] : '';
                if ($check === null) {
                    $result = tyn_check($secret, $token, array('base_url' => $base, 'mode' => 'soft'));
                } else {
                    $result = call_user_func($check, $secret, $token, $base);
                }
                $track(empty($result['allow']) ? 'failed' : 'passed', 'default');
                if (empty($result['allow'])) {
                    $p = $page($return, 'retry', true);
                    return $p === false ? array('type' => 'redirect', 'status' => 302, 'body' => '', 'reason' => 'retry_failed',
                        'headers' => array('Cache-Control: no-store, private, max-age=0', 'Location: ' . $return)) : $p;
                }
                $life = (isset($result['reason']) && $result['reason'] === 'ok') ? $ttl : TYN_GATE_SHORT_TTL;
                $net = $bind ? tyn_gate_net($ip) : '';
                return array('type' => 'redirect', 'status' => 302, 'body' => '', 'reason' => 'pass',
                    'headers' => array(
                        'Cache-Control: no-store, private, max-age=0',
                        tyn_gate_cookie_header(tyn_gate_mark_make($secret, $life, $net, $now), $life, $secure, $cookiePath, $now),
                        'Location: ' . $return,
                    ));
            }

            // Винятки шляхів: типові, з налаштувань і регулярні вирази.
            $patterns = array_merge(tyn_gate_default_paths(), isset($cfg['paths']) && is_array($cfg['paths']) ? $cfg['paths'] : array());
            if (tyn_gate_route_match($path === '' ? '/' : $path, $patterns) || tyn_gate_regex_match($path . $query, isset($cfg['regex']) ? $cfg['regex'] : array())) {
                $none['reason'] = 'path';
                return $none;
            }
            $decision = tyn_gate_decide($server, $cookies, array(
                'mode' => $mode, 'secret' => $secret, 'route' => '',
                'logged_in' => !empty($cfg['logged_in']),
                'bots' => isset($cfg['bots']) && is_array($cfg['bots']) ? $cfg['bots'] : array(),
                'bind' => $bind, 'ip' => $ip, 'now' => $now,
                'cli' => isset($cfg['cli']) ? (bool) $cfg['cli'] : (PHP_SAPI === 'cli'),
                // Справжність названих ботів: типово вмикається, коли є тека (bot_dir чи buffer_dir чи track_dir); bot_verify=false вимикає.
                'bot_dir' => (isset($cfg['bot_verify']) && !$cfg['bot_verify']) ? '' : tyn_gate_first_dir($cfg),
                'bot_ip' => $ip,
                'bot_resolver' => isset($cfg['bot_resolver']) ? $cfg['bot_resolver'] : null,
                'bot_nameservers' => isset($cfg['bot_nameservers']) ? $cfg['bot_nameservers'] : array(),
            ));
            $none['reason'] = $decision['reason'];
            if ($mode === 'observe') {
                if (!empty($cfg['buffer_dir'])) {
                    tyn_gate_observe_record($cfg['buffer_dir'], $decision, $now);
                }
                if (!empty($decision['would_all'])) {
                    $track('would_show', !empty($decision['would_suspicious']) ? $decision['would_suspicious'] : 'default');
                }
                return $none;
            }
            if (empty($decision['show'])) {
                return $none;
            }
            $return = tyn_gate_safe_return($path . $query);
            // Той самий URL відкрито зі сторінки заслону, але позначки немає: cookie не працюють, автовідправлення вимкнено.
            $loop = false;
            $ref = isset($server['HTTP_REFERER']) && is_string($server['HTTP_REFERER']) ? @parse_url($server['HTTP_REFERER']) : false;
            $cur = @parse_url($return);
            if (is_array($ref) && is_array($cur) && isset($ref['path'], $cur['path'], $ref['host']) && $ref['path'] === $cur['path']
                && (isset($ref['query']) ? $ref['query'] : '') === (isset($cur['query']) ? $cur['query'] : '')
                && isset($server['HTTP_HOST']) && strtolower($ref['host']) === strtolower(preg_replace('/:\d+$/', '', (string) $server['HTTP_HOST']))) {
                $loop = true;
            }
            $p = $page($return, $loop ? 'cookies' : '', !$loop);
            if ($p !== false) {
                $track('shown', $decision['reason']);
            }
            return $p === false ? $none : $p;
        } catch (Exception $e) {
            return $none;
        } catch (Throwable $e) {
            return $none;
        }
    }

    /** Виводить відповідь tyn_gate_process() і завершує запит. Для type=none нічого не робить. */
    function tyn_gate_send(array $resp)
    {
        if (!isset($resp['type']) || ($resp['type'] !== 'page' && $resp['type'] !== 'redirect') || headers_sent()) {
            return;
        }
        if (function_exists('http_response_code')) {
            http_response_code((int) $resp['status']);
        } else {
            header('X-Tyn-Gate: 1', true, (int) $resp['status']);
        }
        foreach ($resp['headers'] as $h) {
            header($h, strncasecmp($h, 'Set-Cookie', 10) !== 0);
        }
        echo $resp['body'];
        exit;
    }

    // ---------------------------------------------------------------------
    // Серверні події поведінки: POST {base}/v1/track (специфікація behavior-events, schema_v 1).
    // Події складаються в пам'яті запиту (tyn_track_add); tyn_track_flush дописує їх у файловий буфер
    // (flock, 1 МБ), а пачка йде після відповіді відвідувачеві не частіше ніж раз на 15 с і повторюється
    // після збою до 24 год. Мережі на гарячому шляху немає, збій мовчазний.
    // Передаються лише поля з переліку нижче (невідомі відкидаються): паролів, карток, адреси доставки,
    // коментарів і довільних полів тут немає за побудовою. Контакти (email, phone, name) лише в identify
    // і лише коли власник ввімкнув опцію contacts.
    // ---------------------------------------------------------------------

    /** Найбільше подій у пачці й у пам'яті запиту. */
    define('TYN_TRACK_MAX', 500);
    /** Найбільше позицій у замовленні. */
    define('TYN_TRACK_MAX_ITEMS', 40);

    /** Сховище подій запиту: op 'add' | 'take' | 'count' | 'reset'. */
    function tyn_track_store($op, $event = null)
    {
        static $events = array();
        if ($op === 'add') {
            if (count($events) >= TYN_TRACK_MAX) {
                return false;
            }
            $events[] = $event;
            return true;
        }
        if ($op === 'take') {
            $out = $events;
            $events = array();
            return $out;
        }
        if ($op === 'reset') {
            $events = array();
            return true;
        }
        return count($events);
    }

    /** HMAC-посилання на замовлення: перші 16 hex HMAC-SHA256(номер) за секретом сайту. Номер у відкритому вигляді не передається. */
    function tyn_order_ref($secret, $number)
    {
        return substr(hash_hmac('sha256', (string) $number, (string) $secret), 0, 16);
    }

    /** Ціле в межах 0…10^12 або null. Приймає int і рядок із цифр. */
    function tyn_track_money($v)
    {
        if (is_string($v) && preg_match('/^\d{1,13}$/', $v)) {
            $v = (int) $v;
        }
        if (!is_int($v) || $v < 0 || $v > 1000000000000) {
            return null;
        }
        return $v;
    }

    /** Ідентифікатор товару чи ланцюжка: [A-Za-z0-9_.-], 1…64, або null. */
    function tyn_track_id($v)
    {
        if (is_int($v)) {
            $v = (string) $v;
        }
        return (is_string($v) && preg_match('/^[A-Za-z0-9_.-]{1,64}$/', $v)) ? $v : null;
    }

    /** Список ідентифікаторів категорій: до 5 коректних. */
    function tyn_track_ids($v)
    {
        $out = array();
        foreach (is_array($v) ? $v : array() as $x) {
            $id = tyn_track_id($x);
            if ($id !== null && count($out) < 5) {
                $out[] = $id;
            }
        }
        return $out;
    }

    /**
     * Перевіряє й нормалізує одну подію за специфікацією. Повертає масив події (без dt) або null.
     * Невідомі типи й поля відкидаються; невалідний обов'язковий атрибут відкидає подію, необов'язковий випадає.
     */
    function tyn_track_normalize($type, $f)
    {
        if (!is_string($type) || !is_array($f)) {
            return null;
        }
        $ev = array('t' => $type);
        if ($type === 'order' || $type === 'order_status') {
            $ref = isset($f['ref']) && is_string($f['ref']) && preg_match('/^[0-9a-f]{16}$/', $f['ref']) ? $f['ref'] : null;
            if ($ref === null) {
                return null;
            }
            $ev['ref'] = $ref;
            if ($type === 'order_status') {
                $st = isset($f['status']) && is_string($f['status']) ? $f['status'] : '';
                if (!in_array($st, array('paid', 'cancelled', 'refunded'), true)) {
                    return null;
                }
                $ev['status'] = $st;
                return $ev;
            }
            $amount = isset($f['amount']) ? tyn_track_money($f['amount']) : null;
            $cur = isset($f['cur']) && is_string($f['cur']) && preg_match('/^[A-Z]{3}$/', $f['cur']) ? $f['cur'] : null;
            if ($amount === null || $cur === null) {
                return null;
            }
            $ev['amount'] = $amount;
            $ev['cur'] = $cur;
            $items = array();
            foreach (isset($f['items']) && is_array($f['items']) ? $f['items'] : array() as $it) {
                if (!is_array($it) || count($items) >= TYN_TRACK_MAX_ITEMS) {
                    continue;
                }
                $it = array_values($it);
                $id = isset($it[0]) ? tyn_track_id($it[0]) : null;
                $qty = isset($it[1]) && (is_int($it[1]) || (is_string($it[1]) && ctype_digit($it[1]))) ? (int) $it[1] : -1;
                $price = isset($it[2]) ? tyn_track_money($it[2]) : null;
                if ($id !== null && $qty >= 0 && $qty <= 10000 && $price !== null) {
                    $items[] = array($id, $qty, $price);
                }
            }
            if ($items) {
                $ev['items'] = $items;
            }
            $ship = isset($f['ship']) && is_string($f['ship']) ? $f['ship'] : '';
            $ev['ship'] = in_array($ship, array('pickup', 'courier', 'post', 'locker', 'other'), true) ? $ship : 'other';
            $pay = isset($f['pay']) && is_string($f['pay']) ? $f['pay'] : '';
            $ev['pay'] = in_array($pay, array('card', 'cod', 'bank', 'wallet', 'crypto', 'other'), true) ? $pay : 'other';
            return $ev;
        }
        if ($type === 'gate') {
            $dec = isset($f['decision']) && is_string($f['decision']) ? $f['decision'] : '';
            if (!in_array($dec, array('would_show', 'shown', 'passed', 'failed'), true)) {
                return null;
            }
            $reason = isset($f['reason']) && is_string($f['reason']) ? $f['reason'] : 'default';
            $ev['decision'] = $dec;
            $ev['reason'] = in_array($reason, array('score', 'bot_ua', 'net_class', 'rate', 'rule', 'trap', 'clickfraud', 'geo', 'manual', 'default', 'fake_bot'), true) ? $reason : 'other';
            $chain = isset($f['chain']) ? tyn_track_id($f['chain']) : null;
            if ($chain !== null) {
                $ev['chain'] = $chain;
            }
            return $ev;
        }
        if ($type === TYN_TRACK_EV_IDENTIFY) {
            $uid = isset($f[TYN_TRACK_F_UID]) ? tyn_track_id($f[TYN_TRACK_F_UID]) : null;
            $cid = isset($f[TYN_TRACK_F_CID]) && is_string($f[TYN_TRACK_F_CID]) && preg_match('/^[0-9a-f]{32}$/', $f[TYN_TRACK_F_CID]) ? $f[TYN_TRACK_F_CID] : null;
            if ($uid !== null) {
                $ev[TYN_TRACK_F_UID] = $uid;
            }
            if ($cid !== null) {
                $ev[TYN_TRACK_F_CID] = $cid;
            }
            // Контакти лише текстом коректної довжини; чи передавати їх, вирішує tyn_track_flush (опція contacts).
            $clean = function ($v, $max) {
                $v = is_string($v) ? trim(preg_replace('/[\x00-\x1f\x7f]/', '', $v)) : '';
                return ($v !== '' && strlen($v) <= $max) ? $v : null;
            };
            $email = isset($f['email']) ? $clean($f['email'], 254) : null;
            if ($email !== null && strpos($email, '@') !== false) {
                $ev['email'] = $email;
            }
            $phone = isset($f['phone']) ? $clean($f['phone'], 32) : null;
            if ($phone !== null) {
                $ev['phone'] = $phone;
            }
            $name = isset($f['name']) ? $clean($f['name'], 120) : null;
            if ($name !== null) {
                $ev['name'] = $name;
            }
            return $ev;
        }
        if ($type === 'catalog') {
            $id = isset($f['id']) ? tyn_track_id($f['id']) : null;
            $price = isset($f['price']) ? tyn_track_money($f['price']) : null;
            if ($id === null || $price === null) {
                return null;
            }
            $ev['id'] = $id;
            $ev['price'] = $price;
            $cat = tyn_track_ids(isset($f['cat']) ? $f['cat'] : array());
            if ($cat) {
                $ev['cat'] = $cat;
            }
            if (isset($f['cur']) && is_string($f['cur']) && preg_match('/^[A-Z]{3}$/', $f['cur'])) {
                $ev['cur'] = $f['cur'];
            }
            $ev['stock'] = (!isset($f['stock']) || $f['stock'] === true || $f['stock'] === 1 || $f['stock'] === '1') ? 1 : 0;
            return $ev;
        }
        return null;
    }

    /** Додає подію в пам'ять запиту. Повертає true, якщо прийнято (тип і поля коректні, ліміт не вичерпано). */
    function tyn_track_add($type, $fields = array())
    {
        try {
            $ev = tyn_track_normalize($type, $fields);
            if ($ev === null) {
                return false;
            }
            $ev['_ms'] = (int) round(microtime(true) * 1000);
            return tyn_track_store('add', $ev);
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Не більше одного рядка в журнал на хвилину (маркер у тимчасовій теці, інакше лише в межах процесу). */
    function tyn_track_log($detail)
    {
        static $last = 0;
        $now = time();
        if ($now - $last < 60) {
            return;
        }
        $last = $now;
        $mark = rtrim((string) @sys_get_temp_dir(), '/\\') . '/tyn-track-log';
        $m = @filemtime($mark);
        if ($m !== false && $now - $m < 60) {
            return;
        }
        @touch($mark);
        tyn_log('track', $detail);
    }

    /** Поля контексту відвідувача в пачці й подіях /v1/track. Назви в одному місці (узгоджені із задачами сервісу E30-034/035). */
    define('TYN_TRACK_F_IP', 'ip');    // повна публічна адреса клієнта
    define('TYN_TRACK_F_NET', 'net');  // мережа /24 чи /48 (сумісність)
    define('TYN_TRACK_F_UA', 'ua');    // сирий User-Agent до 512 символів
    define('TYN_TRACK_F_CID', 'vc');   // постійний ідентифікатор браузера (cookie tyn_cid)
    define('TYN_TRACK_F_UID', 'uid');  // ідентифікатор покупця магазину (якщо ввійшов)
    /** Подія «хто це»: uid, cid і (лише з дозволу власника) email, phone, name. */
    define('TYN_TRACK_EV_IDENTIFY', 'identify');
    /** Назва першосторонньої cookie з постійним ідентифікатором (її читає t.js, тому без HttpOnly). */
    define('TYN_TRACK_COOKIE', 'tyn_cid');
    /** Ключ cookie-ідентифікатора в записах /v1/traffic (поле cid там уже зайняте типом рекламної мітки). */
    define('TYN_TRAFFIC_F_CID', 'vc');
    /** Буфер подій: інтервал між надсиланнями (с), розмір (байт), вік події (с), тіло пачки (байт), розкид dt (мс). */
    define('TYN_TRACK_INTERVAL', 15);
    define('TYN_TRACK_BUF_MAX', 1048576);
    define('TYN_TRACK_MAX_AGE', 86400);
    define('TYN_TRACK_MAX_BODY', 245760);
    define('TYN_TRACK_SPAN_MS', 900000);

    /** Ключі контексту, що йдуть у пачку. */
    function tyn_track_ctx_keys()
    {
        return array(TYN_TRACK_F_IP, TYN_TRACK_F_NET, TYN_TRACK_F_UA, TYN_TRACK_F_CID, TYN_TRACK_F_UID);
    }

    /**
     * Повна адреса, якщо вона не службова (приватні, loopback, link-local, CGNAT, multicast і зарезервовані діапазони
     * не передаються), інакше ''. Перевірка власна, щоб не залежати від версії PHP.
     */
    function tyn_public_ip($ip)
    {
        $ip = is_string($ip) ? trim($ip) : '';
        $bin = $ip === '' ? false : tyn_traffic_bin($ip);
        if ($bin === false) {
            return '';
        }
        $b = array_values(unpack('C*', $bin));
        if (count($b) === 4) {
            if ($b[0] === 0 || $b[0] === 10 || $b[0] === 127 || $b[0] >= 224
                || ($b[0] === 100 && $b[1] >= 64 && $b[1] <= 127)
                || ($b[0] === 169 && $b[1] === 254)
                || ($b[0] === 172 && $b[1] >= 16 && $b[1] <= 31)
                || ($b[0] === 192 && $b[1] === 168)) {
                return '';
            }
            return $ip;
        }
        $zero = true;
        for ($i = 0; $i < 15; $i++) {
            if ($b[$i] !== 0) {
                $zero = false;
                break;
            }
        }
        if ($zero && $b[15] <= 1) { // :: і ::1
            return '';
        }
        if ($b[0] === 0xff || ($b[0] & 0xfe) === 0xfc || ($b[0] === 0xfe && ($b[1] & 0xc0) === 0x80)) { // multicast, ULA, link-local
            return '';
        }
        return $ip;
    }

    /** Ідентифікатор браузера з cookie запиту (32 hex) або ''. */
    function tyn_cid_from_server(array $server)
    {
        $c = isset($server['HTTP_COOKIE']) && is_string($server['HTTP_COOKIE']) ? $server['HTTP_COOKIE'] : '';
        return preg_match('/(?:^|;\s*)' . TYN_TRACK_COOKIE . '=([0-9a-f]{32})(?:;|$)/', $c, $m) ? $m[1] : '';
    }

    /**
     * Постійний ідентифікатор браузера: бере з cookie tyn_cid, а якщо її немає, створює випадкові 128 біт і
     * ставить на 2 роки (SameSite=Lax, Secure на https, без HttpOnly). Cookie не ставиться в CLI й після виводу.
     */
    function tyn_cid_ensure(array $server)
    {
        $cid = tyn_cid_from_server($server);
        if ($cid !== '') {
            return $cid;
        }
        if (function_exists('random_bytes')) {
            $cid = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $cid = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $cid = md5(uniqid((string) mt_rand(), true));
        }
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            $https = (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off')
                || (isset($server['SERVER_PORT']) && (int) $server['SERVER_PORT'] === 443)
                || (isset($server['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $server['HTTP_X_FORWARDED_PROTO']) === 'https');
            @header('Set-Cookie: ' . TYN_TRACK_COOKIE . '=' . $cid . '; Max-Age=63072000; Expires=' . gmdate('D, d M Y H:i:s', time() + 63072000)
                . ' GMT; Path=/; SameSite=Lax' . ($https ? '; Secure' : ''), false);
        }
        return $cid;
    }

    /**
     * Контекст відвідувача для подій: ip (повна публічна), net, ua (сирий, до 512), cid, uid. Порожній масив, якщо
     * no_net (дія не від відвідувача) чи немає $o['server']. $o: server, proxy_header, trusted_proxies, uid,
     * set_cid (типово так: без cookie вона створюється).
     */
    function tyn_track_ctx(array $o)
    {
        $ctx = array();
        if (!empty($o['no_net']) || empty($o['server']) || !is_array($o['server'])) {
            return $ctx;
        }
        $server = $o['server'];
        $ip = tyn_traffic_client_ip($server, array(
            'proxy_header' => isset($o['proxy_header']) ? $o['proxy_header'] : '',
            'trusted_proxies' => isset($o['trusted_proxies']) && is_array($o['trusted_proxies']) ? $o['trusted_proxies'] : array(),
        ));
        $net = tyn_traffic_net($ip);
        if ($net !== '') {
            $ctx[TYN_TRACK_F_NET] = $net;
        }
        $pub = tyn_public_ip($ip);
        if ($pub !== '') {
            $ctx[TYN_TRACK_F_IP] = $pub;
        }
        $ua = isset($server['HTTP_USER_AGENT']) && is_string($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : '';
        $ua = (string) substr(preg_replace('/[\x00-\x1f\x7f]/', '', $ua), 0, 512);
        if ($ua !== '') {
            $ctx[TYN_TRACK_F_UA] = $ua;
        }
        $cid = (!isset($o['set_cid']) || $o['set_cid']) ? tyn_cid_ensure($server) : tyn_cid_from_server($server);
        if ($cid !== '') {
            $ctx[TYN_TRACK_F_CID] = $cid;
        }
        $uid = isset($o['uid']) ? tyn_track_id($o['uid']) : null;
        if ($uid !== null) {
            $ctx[TYN_TRACK_F_UID] = $uid;
        }
        return $ctx;
    }

    /**
     * Збирає тіло пачки без мережі. $events: події з _ms (мс) і необов'язковим _c (контекст відвідувача цієї події).
     * Ключ контексту, однаковий в усіх подіях, іде на рівень пачки; різний лишається в самих подіях (подія перекриває пачку).
     * $o: key, secret. Повертає JSON-рядок або '' (подій немає, ключ чи секрет порожні).
     */
    function tyn_track_body(array $events, array $o)
    {
        $key = isset($o['key']) && is_string($o['key']) ? $o['key'] : '';
        $secret = isset($o['secret']) && is_string($o['secret']) ? $o['secret'] : '';
        if (!$events || $key === '' || $secret === '') {
            return '';
        }
        $events = array_slice(array_values($events), 0, TYN_TRACK_MAX);
        $min = null;
        foreach ($events as $e) {
            $min = ($min === null || $e['_ms'] < $min) ? $e['_ms'] : $min;
        }
        $body = array('key' => $key, 'secret' => $secret, 'v' => 1);
        $uniform = array();
        foreach (tyn_track_ctx_keys() as $k) {
            $first = isset($events[0]['_c'][$k]) ? $events[0]['_c'][$k] : null;
            $same = true;
            foreach ($events as $e) {
                $v = isset($e['_c'][$k]) ? $e['_c'][$k] : null;
                if ($v !== $first) {
                    $same = false;
                    break;
                }
            }
            if ($same) {
                if ($first !== null) {
                    $body[$k] = $first;
                }
                $uniform[$k] = true;
            }
        }
        $out = array();
        foreach ($events as $e) {
            $ctx = isset($e['_c']) && is_array($e['_c']) ? $e['_c'] : array();
            $ms = $e['_ms'];
            unset($e['_ms'], $e['_c']);
            foreach ($ctx as $k => $v) {
                if (empty($uniform[$k]) && !isset($e[$k])) {
                    $e[$k] = $v;
                }
            }
            $e['dt'] = max(0, min(TYN_TRACK_SPAN_MS, $ms - $min));
            $out[] = $e;
        }
        $body['events'] = $out;
        $json = @json_encode($body);
        return is_string($json) ? $json : '';
    }

    /** POST пачки на {base}/v1/track; true лише для 200 {"ok":true}. */
    function tyn_track_post($base, $body)
    {
        $resp = tyn_http_post($base . '/v1/track', $body, 1, 1, 'application/json');
        $json = ($resp['status'] === 200) ? json_decode($resp['body'], true) : null;
        if (is_array($json) && isset($json['ok']) && $json['ok'] === true) {
            return true;
        }
        tyn_track_log($resp['status'] === 0 ? $resp['error'] : 'http ' . $resp['status']);
        return false;
    }

    /** Файли буфера подій у теці $dir. */
    function tyn_track_files($dir)
    {
        $dir = rtrim((string) $dir, '/\\') . '/';
        return array('dir' => $dir, 'buf' => $dir . 'tyn-track.jsonl', 'lock' => $dir . 'tyn-track.lock', 'drop' => $dir . 'tyn-track.drop');
    }

    /** Скільки подій у буфері й скільки відкинуто через переповнення (для адмінки й тестів). */
    function tyn_track_stats($dir)
    {
        $f = tyn_track_files($dir);
        $raw = is_file($f['buf']) ? (string) @file_get_contents($f['buf']) : '';
        $drop = is_file($f['drop']) ? (int) trim((string) @file_get_contents($f['drop'])) : 0;
        return array('pending' => $raw === '' ? 0 : substr_count($raw, "\n"), 'dropped' => $drop);
    }

    /**
     * Дописує події в буфер (один рядок JSON на подію, flock). Межа 1 МБ: що не влізло, відкидається з лічильником.
     * $entries: події з _ms і _c. Повертає кількість дописаних.
     */
    function tyn_track_buffer_add($dir, array $entries)
    {
        $f = tyn_track_files($dir);
        $old = umask(0077);
        if (!is_dir($f['dir'])) {
            @mkdir($f['dir'], 0700, true);
            @file_put_contents($f['dir'] . '.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
        $fp = @fopen($f['buf'], 'ab');
        umask($old);
        if (!$fp) {
            return 0;
        }
        $n = 0;
        $dropped = 0;
        if (@flock($fp, LOCK_EX)) {
            clearstatcache(true, $f['buf']);
            $size = (int) @filesize($f['buf']);
            foreach ($entries as $e) {
                $ms = $e['_ms'];
                $c = isset($e['_c']) && is_array($e['_c']) ? $e['_c'] : array();
                unset($e['_ms'], $e['_c']);
                $line = @json_encode(array('ms' => $ms, 'c' => $c ? $c : new stdClass(), 'e' => $e));
                if (!is_string($line) || $size + strlen($line) + 1 > TYN_TRACK_BUF_MAX) {
                    $dropped++;
                    continue;
                }
                if (@fwrite($fp, $line . "\n") === strlen($line) + 1) {
                    $size += strlen($line) + 1;
                    $n++;
                }
            }
            @fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            $dropped = count($entries);
        }
        fclose($fp);
        if ($dropped > 0) {
            $d = @fopen($f['drop'], 'c+b');
            if ($d && @flock($d, LOCK_EX)) {
                $cur = (int) trim((string) fread($d, 20));
                ftruncate($d, 0);
                rewind($d);
                fwrite($d, (string) ($cur + $dropped));
                flock($d, LOCK_UN);
            }
            if ($d) {
                fclose($d);
            }
            tyn_track_log('буфер подій переповнено, відкинуто ' . $dropped);
        }
        return $n;
    }

    /**
     * Надсилає одну пачку з буфера: найстаріші події (до 500, розкид часу до 15 хв, тіло до 240 КБ). Не частіше ніж раз на
     * interval секунд (типово 15; 0 лише для тестів і CLI). Успіх прибирає надіслане з буфера; збій (немає зв'язку, 429, 503)
     * лишає події до наступної спроби; події старші за 24 год відкидаються.
     * $o: key, secret, base_url, buffer_dir, interval, now_s (час у секундах, для тестів).
     * Повертає true, якщо сервіс прийняв пачку.
     */
    function tyn_track_pump(array $o)
    {
        $lock = null;
        try {
            $base = tyn_normalize_base(isset($o['base_url']) ? $o['base_url'] : '');
            $dir = isset($o['buffer_dir']) && is_string($o['buffer_dir']) ? $o['buffer_dir'] : '';
            if ($base === '' || $dir === '') {
                return false;
            }
            $f = tyn_track_files($dir);
            clearstatcache();
            if (!is_file($f['buf']) || !@filesize($f['buf'])) {
                return false;
            }
            $now = isset($o['now_s']) ? (int) $o['now_s'] : time();
            $interval = isset($o['interval']) ? max(0, min(3600, (int) $o['interval'])) : TYN_TRACK_INTERVAL;
            $old = umask(0077);
            $lock = @fopen($f['lock'], 'c+b');
            umask($old);
            if (!$lock || !@flock($lock, LOCK_EX | LOCK_NB)) {
                return false;
            }
            $last = (int) trim((string) fread($lock, 32));
            if ($interval > 0 && $last > 0 && $now >= $last && $now - $last < $interval) {
                return false;
            }
            ftruncate($lock, 0);
            rewind($lock);
            fwrite($lock, (string) $now);
            fflush($lock);

            $raw = (string) @file_get_contents($f['buf']);
            $lines = $raw === '' ? array() : explode("\n", rtrim($raw, "\n"));
            $batch = array();
            $idx = array();
            $dead = 0; // старі чи зіпсовані рядки на початку буфера
            $min = null;
            $max = null;
            foreach ($lines as $i => $line) {
                $r = json_decode($line, true);
                $ok = is_array($r) && isset($r['ms'], $r['e']) && is_int($r['ms']) && is_array($r['e']);
                if (!$ok || ($now * 1000 - $r['ms']) > TYN_TRACK_MAX_AGE * 1000) {
                    if (!$batch) {
                        $dead = $i + 1;
                    }
                    continue;
                }
                $mn = $min === null ? $r['ms'] : min($min, $r['ms']);
                $mx = $max === null ? $r['ms'] : max($max, $r['ms']);
                if (count($batch) >= TYN_TRACK_MAX || $mx - $mn > TYN_TRACK_SPAN_MS) {
                    break;
                }
                $min = $mn;
                $max = $mx;
                $e = $r['e'];
                $e['_ms'] = $r['ms'];
                $e['_c'] = isset($r['c']) && is_array($r['c']) ? $r['c'] : array();
                $batch[] = $e;
                $idx[] = $i;
            }
            $sent = false;
            $consumed = $dead;
            if ($batch) {
                $n = count($batch);
                $body = tyn_track_body($batch, $o);
                while ($body !== '' && strlen($body) > TYN_TRACK_MAX_BODY && $n > 1) {
                    $n = (int) floor($n / 2);
                    $body = tyn_track_body(array_slice($batch, 0, $n), $o);
                }
                if ($body !== '' && strlen($body) <= TYN_TRACK_MAX_BODY) {
                    $sent = tyn_track_post($base, $body);
                    if ($sent) {
                        $consumed = $idx[$n - 1] + 1; // збій: події лишаються до наступного разу
                    }
                }
            }
            if ($consumed > 0) {
                $fp = @fopen($f['buf'], 'r+b');
                if ($fp && @flock($fp, LOCK_EX)) {
                    $cur = (string) stream_get_contents($fp);
                    $curLines = $cur === '' ? array() : explode("\n", rtrim($cur, "\n"));
                    $rest = array_slice($curLines, $consumed);
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, $rest ? implode("\n", $rest) . "\n" : '');
                    fflush($fp);
                    flock($fp, LOCK_UN);
                }
                if ($fp) {
                    fclose($fp);
                }
            }
            return $sent;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        } finally {
            if (is_resource($lock)) {
                @flock($lock, LOCK_UN);
                @fclose($lock);
            }
        }
    }

    /**
     * Передає накопичені події. $o: key, secret, base_url, server, proxy_header, trusted_proxies, uid, no_net,
     * contacts (чи лишати email/phone/name в identify; типово ні), buffer_dir (файловий буфер: події дописуються одразу,
     * пачка йде після відповіді не частіше ніж раз на interval секунд і повторюється після збоїв до 24 год), interval,
     * now (true: надіслати негайно без буфера, для CLI й тестів). Без buffer_dir: одна пачка після відповіді, без повторів.
     * Тайм-аут 1 с, збій тихий. Повертає true, якщо події прийнято в роботу.
     */
    function tyn_track_flush(array $o = array())
    {
        static $registered = false;
        static $pumpOpts = null;
        try {
            $events = tyn_track_store('take');
            if (!$events) {
                return false;
            }
            $ctx = tyn_track_ctx($o);
            $keep = array();
            foreach ($events as $e) {
                if ($e['t'] === TYN_TRACK_EV_IDENTIFY) {
                    foreach (array(TYN_TRACK_F_UID, TYN_TRACK_F_CID) as $k) {
                        if (!isset($e[$k]) && isset($ctx[$k])) {
                            $e[$k] = $ctx[$k];
                        }
                    }
                    if (empty($o['contacts'])) {
                        unset($e['email'], $e['phone'], $e['name']);
                    }
                    if (!isset($e[TYN_TRACK_F_UID]) && !isset($e[TYN_TRACK_F_CID])) {
                        continue;
                    }
                }
                $e['_c'] = $ctx;
                $keep[] = $e;
            }
            if (!$keep) {
                return false;
            }
            if (!empty($o['now'])) {
                $base = tyn_normalize_base(isset($o['base_url']) ? $o['base_url'] : '');
                $body = $base === '' ? '' : tyn_track_body($keep, $o);
                return $body !== '' && tyn_track_post($base, $body);
            }
            $buffered = !empty($o['buffer_dir']) && is_string($o['buffer_dir']);
            if ($buffered) {
                if (tyn_track_buffer_add($o['buffer_dir'], $keep) < 1) {
                    return false;
                }
            } else {
                foreach ($keep as $e) {
                    tyn_track_store('add', $e); // без буфера події тримаємо в пам'яті до кінця запиту
                }
            }
            $pumpOpts = $o;
            if (!$registered) {
                $registered = true;
                register_shutdown_function(function () use (&$pumpOpts) {
                    try {
                        $o = $pumpOpts;
                        if (!is_array($o)) {
                            return;
                        }
                        $buffered = !empty($o['buffer_dir']) && is_string($o['buffer_dir']);
                        if (!$buffered && tyn_track_store('count') < 1) {
                            return;
                        }
                        if (function_exists('fastcgi_finish_request')) {
                            @fastcgi_finish_request();
                        } elseif (function_exists('litespeed_finish_request')) {
                            @litespeed_finish_request();
                        }
                        if ($buffered) {
                            tyn_track_pump($o);
                            return;
                        }
                        $base = tyn_normalize_base(isset($o['base_url']) ? $o['base_url'] : '');
                        $events = tyn_track_store('take');
                        $body = $base === '' ? '' : tyn_track_body($events, $o);
                        if ($body !== '') {
                            tyn_track_post($base, $body);
                        }
                    } catch (Exception $e) {
                    } catch (Throwable $e) {
                    }
                });
            }
            return true;
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** Тека кешу ботів: bot_dir, інакше buffer_dir, інакше track_dir (порожньо, якщо жодної). */
    function tyn_gate_first_dir(array $cfg)
    {
        foreach (array('bot_dir', 'buffer_dir', 'track_dir') as $k) {
            if (isset($cfg[$k]) && is_string($cfg[$k]) && $cfg[$k] !== '') {
                return $cfg[$k];
            }
        }
        return '';
    }

    /** Причина рішення заслону (з tyn_gate_decide) у перелік специфікації. */
    function tyn_gate_track_reason($code)
    {
        $code = is_string($code) ? $code : '';
        if (strpos($code, 'suspicious:') === 0) {
            $code = substr($code, 11);
        }
        if (strpos($code, 'ua_') === 0) {
            return 'bot_ua';
        }
        if ($code === 'no_language' || $code === 'automation' || $code === 'no_accept') {
            return 'rule';
        }
        if ($code === 'fake_bot') {
            return 'fake_bot';
        }
        return 'default';
    }

    /** Мінімальні одиниці валюти за ISO 4217 (1234,50 UAH дає 123450). */
    function tyn_track_minor($amount, $cur)
    {
        $cur = strtoupper((string) $cur);
        $places = 2;
        if (in_array($cur, array('BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'PYG', 'RWF', 'UGX', 'UYI', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'), true)) {
            $places = 0;
        } elseif (in_array($cur, array('BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND'), true)) {
            $places = 3;
        }
        return (int) round((float) $amount * pow(10, $places));
    }

    /** Спосіб доставки за кодом магазину (лише довідник значень; невідоме дає other). */
    function tyn_track_guess_ship($code)
    {
        $c = strtolower((string) $code);
        if (preg_match('/pickup|self|local_pickup|warehouse/', $c)) {
            return 'pickup';
        }
        if (preg_match('/locker|postomat|parcel_machine/', $c)) {
            return 'locker';
        }
        if (preg_match('/novaposhta|nova_poshta|ukrposhta|post|meest|justin|delivery_auto/', $c)) {
            return 'post';
        }
        if (preg_match('/flat|free|weight|item|courier|table|per_/', $c)) {
            return 'courier';
        }
        return 'other';
    }

    /** Спосіб оплати за кодом магазину. */
    function tyn_track_guess_pay($code)
    {
        $c = strtolower((string) $code);
        if (preg_match('/^cod|cash_on|cashondelivery|nalozh/', $c)) {
            return 'cod';
        }
        if (preg_match('/bank|bacs|invoice|transfer|cheque/', $c)) {
            return 'bank';
        }
        if (preg_match('/crypto|bitcoin|coinbase|usdt/', $c)) {
            return 'crypto';
        }
        if (preg_match('/paypal|ppcp|^pp_|wallet|applepay|googlepay|apple_pay|google_pay/', $c)) {
            return 'wallet';
        }
        if (preg_match('/liqpay|wayforpay|stripe|fondy|monobank|portmone|card|cc_|authorize|braintree|checkout/', $c)) {
            return 'card';
        }
        return 'other';
    }

    /** Статус замовлення за назвою (кирилиця й латиниця): paid, cancelled, refunded або порожній рядок (не передавати). */
    function tyn_track_status_guess($name)
    {
        $n = function_exists('mb_strtolower') ? mb_strtolower((string) $name, 'UTF-8') : strtolower((string) $name);
        if (preg_match('/refund|поверн|возврат|повернен/u', $n)) {
            return 'refunded';
        }
        if (preg_match('/cancel|скас|отмен|reversed|denied|failed|expired|voided/u', $n)) {
            return 'cancelled';
        }
        if (preg_match('/complet|processing|shipped|paid|сплач|оплач|виконан|відправ|обробк/u', $n)) {
            return 'paid';
        }
        return '';
    }

    /**
     * Каталог пачками по 500: $rows це масив [id, cat[], price, cur, stock]; кожна пачка йде окремим запитом.
     * Повертає кількість прийнятих сервісом рядків (збій пачки мовчки пропускається).
     */
    function tyn_track_catalog_send(array $rows, array $o)
    {
        $sent = 0;
        foreach (array_chunk($rows, TYN_TRACK_MAX) as $chunk) {
            tyn_track_store('reset');
            $n = 0;
            foreach ($chunk as $r) {
                if (tyn_track_add('catalog', $r)) {
                    $n++;
                }
            }
            $o['now'] = true;
            $o['no_net'] = true;
            if ($n > 0 && tyn_track_flush($o)) {
                $sent += $n;
            }
        }
        return $sent;
    }
}
