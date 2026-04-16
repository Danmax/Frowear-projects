<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$body = request_json();

$email       = trim((string) ($body['email'] ?? ''));
$password    = (string) ($body['password'] ?? '');
$displayName = trim((string) ($body['display_name'] ?? ''));
$role        = trim((string) ($body['role'] ?? 'talent'));

if ($email === '') {
    json_response(['ok' => false, 'message' => 'Email is required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'message' => 'Invalid email address.'], 422);
}

if (strlen($password) < 8) {
    json_response(['ok' => false, 'message' => 'Password must be at least 8 characters.'], 422);
}

$allowedRoles = ['talent', 'company_owner'];
if (!in_array($role, $allowedRoles, true)) {
    json_response(['ok' => false, 'message' => 'Role must be talent or company_owner.'], 422);
}

try {
    $user = fw_create_user($email, $password, $displayName, $role);
} catch (RuntimeException $e) {
    json_response(['ok' => false, 'message' => $e->getMessage()], 409);
}

fw_start_session($user['id']);

$rawToken = fw_create_verification_token($user['id']);

try {
    fw_send_verification_email($user['email'], $user['display_name'], $rawToken);
} catch (Throwable) {
    // Non-fatal: account is created; email delivery failure is silent.
}

json_response([
    'ok'   => true,
    'data' => [
        'user'    => [
            'id'             => $user['id'],
            'email'          => $user['email'],
            'display_name'   => $user['display_name'],
            'role'           => $user['role'],
            'email_verified' => (bool) $user['email_verified'],
        ],
        'message' => 'Account created. Check your email to verify.',
    ],
], 201);
