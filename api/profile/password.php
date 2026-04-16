<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user            = fw_require_auth();
$body            = request_json();
$currentPassword = trim((string) ($body['current_password'] ?? ''));
$newPassword     = trim((string) ($body['new_password'] ?? ''));

if ($currentPassword === '' || $newPassword === '') {
    json_response(['ok' => false, 'message' => 'Both current and new password are required.'], 422);
}

if (strlen($newPassword) < 8) {
    json_response(['ok' => false, 'message' => 'New password must be at least 8 characters.'], 422);
}

$row = db_select_one('SELECT password_hash FROM users WHERE id = ? LIMIT 1', [$user['id']]);

if ($row === null || !fw_verify_password($currentPassword, (string) ($row['password_hash'] ?? ''))) {
    json_response(['ok' => false, 'message' => 'Current password is incorrect.'], 401);
}

fw_update_password($user['id'], $newPassword);

json_response(['ok' => true, 'message' => 'Password updated successfully.']);
