<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$body      = request_json();
$convId    = (int) ($body['conversation_id'] ?? 0);
$messageId = (int) ($body['message_id'] ?? 0);

if ($convId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid conversation_id is required.'], 422);
}

if ($messageId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid message_id is required.'], 422);
}

// Verify user is a participant.
$participant = db_select_one(
    'SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
    [$convId, $userId]
);

if ($participant === null) {
    json_response(['ok' => false, 'message' => 'Conversation not found or access denied.'], 403);
}

db_execute(
    'UPDATE conversation_participants SET last_read_message_id = ? WHERE conversation_id = ? AND user_id = ?',
    [$messageId, $convId, $userId]
);

json_response(['ok' => true, 'data' => null]);
