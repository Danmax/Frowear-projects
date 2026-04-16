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

$body        = request_json();
$convId      = (int) ($body['conversation_id'] ?? 0);
$messageBody = trim((string) ($body['body'] ?? ''));
$messageType = trim((string) ($body['message_type'] ?? 'text'));

if ($convId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid conversation_id is required.'], 422);
}

if ($messageBody === '') {
    json_response(['ok' => false, 'message' => 'Message body is required.'], 422);
}

// Verify user is a participant.
$participant = db_select_one(
    'SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
    [$convId, $userId]
);

if ($participant === null) {
    json_response(['ok' => false, 'message' => 'Conversation not found or access denied.'], 403);
}

$messageId = db_insert(
    'INSERT INTO messages (conversation_id, sender_user_id, body, message_type) VALUES (?, ?, ?, ?)',
    [$convId, $userId, $messageBody, $messageType !== '' ? $messageType : 'text']
);

db_execute(
    'UPDATE conversations SET last_message_at = NOW() WHERE id = ?',
    [$convId]
);

// Notify all other participants.
$otherParticipants = db_select(
    'SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND user_id != ?',
    [$convId, $userId]
);

foreach ($otherParticipants as $p) {
    try {
        fw_notify(
            (int) $p['user_id'],
            'new_message',
            ($user['display_name'] ?? 'Someone') . ' sent you a message',
            substr($messageBody, 0, 80),
            $userId,
            'conversation',
            $convId,
            true
        );
    } catch (Throwable) {
        // Non-fatal.
    }
}

$message = db_select_one(
    'SELECT m.id, m.conversation_id, m.sender_user_id, m.body, m.message_type, m.created_at,
            u.display_name AS sender_name
     FROM messages m
     JOIN users u ON u.id = m.sender_user_id
     WHERE m.id = ?',
    [$messageId]
);

json_response(['ok' => true, 'data' => ['message' => $message]], 201);
