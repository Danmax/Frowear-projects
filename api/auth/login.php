<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$body     = request_json();
$email    = trim((string) ($body['email'] ?? ''));
$password = (string) ($body['password'] ?? '');

$genericError = 'Invalid email or password.';

if ($email === '' || $password === '') {
    json_response(['ok' => false, 'message' => $genericError], 401);
}

$user = fw_find_user_by_email($email);

if ($user === null) {
    json_response(['ok' => false, 'message' => $genericError], 401);
}

// Deleted users are blocked with the same generic message.
if (!empty($user['deleted_at'])) {
    json_response(['ok' => false, 'message' => $genericError], 401);
}

if (!fw_verify_password($password, (string) ($user['password_hash'] ?? ''))) {
    json_response(['ok' => false, 'message' => $genericError], 401);
}

fw_start_session($user['id']);

unset($user['password_hash']);

json_response([
    'ok'   => true,
    'data' => [
        'user' => [
            'id'             => $user['id'],
            'email'          => $user['email'],
            'display_name'   => $user['display_name'],
            'role'           => $user['role'],
            'email_verified' => (bool) $user['email_verified'],
            'avatar_url'     => $user['avatar_url'] ?? null,
        ],
    ],
]);
