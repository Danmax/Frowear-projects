<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$body           = request_json();
$notificationId = isset($body['notification_id']) && $body['notification_id'] !== null
    ? (int) $body['notification_id']
    : null;

fw_mark_notifications_read($userId, $notificationId);

json_response(['ok' => true, 'data' => ['unread_count' => fw_unread_count($userId)]]);
