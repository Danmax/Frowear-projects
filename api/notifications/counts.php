<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$unreadNotifications = fw_unread_count($userId);

$unreadMessages = (int) db_select_one(
    'SELECT COUNT(DISTINCT m.conversation_id) AS cnt
     FROM messages m
     JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id AND cp.user_id = ?
     WHERE m.sender_user_id != ?
       AND m.id > COALESCE(cp.last_read_message_id, 0)
       AND m.deleted_at IS NULL',
    [$userId, $userId]
)['cnt'] ?? 0;

json_response([
    'ok'   => true,
    'data' => [
        'notifications' => $unreadNotifications,
        'messages'      => $unreadMessages,
    ],
]);
