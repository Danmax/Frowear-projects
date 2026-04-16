<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

const FW_WEBHOOK_LOG_FILE = FW_DATA_DIR . '/webhook-requests.log';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response([
        'ok' => true,
        'listener' => 'ready',
        'requires_secret' => webhook_secret() !== '',
    ]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$configuredSecret = webhook_secret();
if ($configuredSecret === '') {
    json_response(['ok' => false, 'message' => 'FW_WEBHOOK_SECRET is not configured on the server.'], 500);
}

$providedSecret = '';
$headers = function_exists('getallheaders') ? getallheaders() : [];

foreach ($headers as $name => $value) {
    if (strtolower((string) $name) === 'x-webhook-secret') {
        $providedSecret = (string) $value;
        break;
    }
}

if ($providedSecret === '') {
    $providedSecret = (string) ($_GET['secret'] ?? $_POST['secret'] ?? '');
}

if (!hash_equals($configuredSecret, $providedSecret)) {
    json_response(['ok' => false, 'message' => 'Invalid webhook secret.'], 401);
}

$rawBody = file_get_contents('php://input');
$decoded = json_decode($rawBody ?: '', true);

$entry = [
    'received_at' => gmdate('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'event' => $_SERVER['HTTP_X_WEBHOOK_EVENT'] ?? $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
    'payload' => is_array($decoded) ? $decoded : ['raw' => (string) $rawBody],
];

if (!is_dir(data_directory()) && !mkdir(data_directory(), 0775, true) && !is_dir(data_directory())) {
    json_response(['ok' => false, 'message' => 'Unable to create webhook data directory.'], 500);
}

$encodedEntry = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($encodedEntry === false || file_put_contents(FW_WEBHOOK_LOG_FILE, $encodedEntry . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
    json_response(['ok' => false, 'message' => 'Unable to write webhook log.'], 500);
}

json_response([
    'ok' => true,
    'message' => 'Webhook received.',
    'event' => $entry['event'],
]);
