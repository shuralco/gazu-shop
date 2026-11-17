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
            foreach (array('page_type', 'event', 'amount', 'currency') as $k) {
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
        $net = tyn_traffic_net(tyn_traffic_client_ip($server, $opts));
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
        $ann = array_merge(tyn_traffic_annotate(), array_intersect_key($opts, array_flip(array('page_type', 'event', 'amount', 'currency'))));
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

        $rec = array(
            'ts' => time(),
            'm' => $method,
            'p' => $path,
            's' => $status,
            'net' => $net,
            'ua' => tyn_traffic_clean(isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : '', 300, true),
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
     *        bots (додаткові підрядки UA), routes (додаткові шаблони маршрутів), bind (bool), ip (адреса клієнта),
     *        now, cli (bool, для тестів).
     * Повертає array('show' => bool, 'reason' => код). Коди «не показувати»: off, cli, method, ajax, not_html,
     * bot, route, logged_in, mark, no_secret; «показувати»: all, suspicious.
     */
    function tyn_gate_decide(array $server, array $cookies, array $opts)
    {
        try {
            $mode = isset($opts['mode']) ? $opts['mode'] : '';
            if ($mode !== 'all' && $mode !== 'suspicious') {
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
            if (tyn_gate_is_allowed_bot($ua, $extraBots)) {
                return array('show' => false, 'reason' => 'bot');
            }
            // Маршрут і шлях (карти сайту, вивантаження).
            $route = isset($opts['route']) && is_string($opts['route']) ? $opts['route'] : '';
            $patterns = array_merge(tyn_gate_default_routes(), isset($opts['routes']) && is_array($opts['routes']) ? $opts['routes'] : array());
            if ($route !== '' && tyn_gate_route_match($route, $patterns)) {
                return array('show' => false, 'reason' => 'route');
            }
            $uri = isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
            $path = strtolower((string) substr($uri, 0, strcspn($uri, '?#')));
            if (preg_match('#\.(xml|yml|json|csv|txt|rss|atom|gz|zip)$#', $path) || strpos($path, 'sitemap') !== false) {
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
                return array('show' => true, 'reason' => 'all');
            }
            // suspicious: ознаки не звичайного браузера.
            $class = tyn_gate_ua_class($ua);
            $why = '';
            if ($class !== 'browser') {
                $why = 'ua_' . $class;
            } elseif (trim($h('Accept-Language')) === '') {
                $why = 'no_language';
            } elseif (preg_match('/headless/i', $h('Sec-CH-UA')) || $h('X-Selenium') !== '' || $h('X-Puppeteer') !== '' || $h('X-Automation') !== '') {
                $why = 'automation';
            } elseif ($accept === '') {
                $why = 'no_accept';
            }
            if ($why !== '') {
                return array('show' => true, 'reason' => 'suspicious:' . $why);
            }
            return array('show' => false, 'reason' => 'browser');
        } catch (Exception $e) {
            return array('show' => false, 'reason' => 'error');
        } catch (Throwable $e) {
            return array('show' => false, 'reason' => 'error');
        }
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
}
