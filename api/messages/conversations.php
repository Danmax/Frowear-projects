<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

// ── GET: list conversations ──────────────────────────────────────────────────

if ($method === 'GET') {
    $conversations = db_select(
        'SELECT c.*,
                cp2.user_id AS other_user_id,
                u2.display_name AS other_name,
                u2.avatar_url AS other_avatar,
                (SELECT body
                 FROM messages m
                 WHERE m.conversation_id = c.id AND m.deleted_at IS NULL
                 ORDER BY m.created_at DESC
                 LIMIT 1) AS last_message,
                (SELECT COUNT(*)
                 FROM messages m
                 JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id AND cp.user_id = ?
                 WHERE m.conversation_id = c.id
                   AND m.id > COALESCE(cp.last_read_message_id, 0)
                   AND m.sender_user_id != ?
                   AND m.deleted_at IS NULL) AS unread_count
         FROM conversations c
         JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
         LEFT JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id != ?
         LEFT JOIN users u2 ON u2.id = cp2.user_id
         WHERE c.deleted_at IS NULL
         ORDER BY c.last_message_at DESC',
        [$userId, $userId, $userId, $userId]
    );

    json_response(['ok' => true, 'conversations' => $conversations]);
}

// ── POST: create or find a direct conversation ───────────────────────────────

$body = request_json();

// Determine if this is a group or direct conversation request.
$isGroup = isset($body['type']) && $body['type'] === 'group';

if ($isGroup) {
    $participantIds = array_map('intval', (array) ($body['participant_ids'] ?? []));
    $title          = trim((string) ($body['title'] ?? ''));

    if (count($participantIds) < 1) {
        json_response(['ok' => false, 'message' => 'At least one participant_id is required for a group conversation.'], 422);
    }

    $convId = db_insert(
        'INSERT INTO conversations (type, title, created_by_user_id) VALUES (?, ?, ?)',
        ['group', $title !== '' ? $title : null, $userId]
    );

    // Add creator as participant.
    db_insert(
        'INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)',
        [$convId, $userId]
    );

    foreach ($participantIds as $participantId) {
        if ($participantId === $userId) {
            continue;
        }
        try {
            db_insert(
                'INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)',
                [$convId, $participantId]
            );
        } catch (Throwable) {
            // Skip duplicate participants silently.
        }
    }

    json_response(['ok' => true, 'conversation_id' => $convId], 201);
}

// Direct conversation.
$participantId = (int) ($body['participant_id'] ?? 0);

if ($participantId <= 0) {
    json_response(['ok' => false, 'message' => 'participant_id is required.'], 422);
}

if ($participantId === $userId) {
    json_response(['ok' => false, 'message' => 'Cannot start a conversation with yourself.'], 422);
}

// Check if a direct conversation already exists between these two users.
$existing = db_select_one(
    'SELECT c.id
     FROM conversations c
     JOIN conversation_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ?
     JOIN conversation_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ?
     WHERE c.type = \'direct\' AND c.deleted_at IS NULL
     LIMIT 1',
    [$userId, $participantId]
);

if ($existing !== null) {
    json_response(['ok' => true, 'conversation_id' => (int) $existing['id']]);
}

// Create new direct conversation.
$convId = db_insert(
    'INSERT INTO conversations (type, created_by_user_id) VALUES (\'direct\', ?)',
    [$userId]
);

db_insert(
    'INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)',
    [$convId, $userId]
);
db_insert(
    'INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)',
    [$convId, $participantId]
);

json_response(['ok' => true, 'conversation_id' => $convId], 201);
