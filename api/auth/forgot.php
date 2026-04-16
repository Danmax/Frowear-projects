<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$body  = request_json();
$email = trim((string) ($body['email'] ?? ''));

// Always return the same response to prevent email enumeration.
$successResponse = [
    'ok'      => true,
    'data'    => null,
    'message' => 'If that email exists, a reset link has been sent.',
];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response($successResponse);
}

$user = fw_find_user_by_email($email);

if ($user !== null && empty($user['deleted_at'])) {
    try {
        $rawToken = fw_create_reset_token($user['id']);
        fw_send_reset_email($user['email'], $user['display_name'], $rawToken);
    } catch (Throwable) {
        // Silent fail — do not reveal whether email delivery failed.
    }
}

json_response($successResponse);
