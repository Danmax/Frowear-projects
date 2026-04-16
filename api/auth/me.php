<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user = fw_require_auth();

unset($user['password_hash']);

json_response([
    'ok'   => true,
    'data' => [
        'user'                 => $user,
        'unread_notifications' => fw_unread_count($user['id']),
    ],
]);
