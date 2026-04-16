<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response([
        'ok' => true,
        'authenticated' => is_admin_authenticated(),
        'content' => get_site_content(),
    ]);
}

$payload = request_json();
$action = (string) ($payload['action'] ?? '');

if ($action === 'login') {
    $configuredKey = admin_key();
    $providedKey = (string) ($payload['key'] ?? '');

    if ($configuredKey === '') {
        json_response(['ok' => false, 'message' => 'FW_ADMIN_KEY is not configured on the server.'], 500);
    }

    if (!hash_equals($configuredKey, $providedKey)) {
        json_response(['ok' => false, 'message' => 'Admin key is invalid.'], 401);
    }

    $_SESSION[FW_ADMIN_SESSION_KEY] = true;
    json_response([
        'ok' => true,
        'authenticated' => true,
        'content' => get_site_content(),
    ]);
}

if ($action === 'logout') {
    unset($_SESSION[FW_ADMIN_SESSION_KEY]);
    json_response(['ok' => true, 'authenticated' => false]);
}

if ($action === 'save') {
    require_admin();

    $content = $payload['content'] ?? null;
    if (!is_array($content)) {
        json_response(['ok' => false, 'message' => 'Invalid content payload.'], 422);
    }

    $merged = merge_deep(default_site_content(), $content);
    save_site_content($merged);

    json_response([
        'ok' => true,
        'message' => 'Content saved.',
        'content' => $merged,
    ]);
}

if ($action === 'reset') {
    require_admin();
    $defaults = default_site_content();
    save_site_content($defaults);

    json_response([
        'ok' => true,
        'message' => 'Default content restored.',
        'content' => $defaults,
    ]);
}

json_response(['ok' => false, 'message' => 'Unsupported action.'], 400);
