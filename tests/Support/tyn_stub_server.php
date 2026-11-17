<?php
// Заглушка сервісу Тин для тестів (php -S 127.0.0.1:порт tyn_stub_server.php): пише запити у файл з env TYN_TEST_LOG.
$body = [];
parse_str((string) file_get_contents('php://input'), $body);
file_put_contents((string) getenv('TYN_TEST_LOG'), json_encode(['uri' => $_SERVER['REQUEST_URI'], 'post' => $body]) . "\n", FILE_APPEND);
http_response_code((int) (getenv('TYN_TEST_STATUS') ?: 200));
header('Content-Type: application/json');
echo '{"ok":true}';
