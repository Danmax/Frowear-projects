<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$convId = (int) ($_GET['conversation_id'] ?? 0);

if ($convId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid conversation_id is required.'], 422);
}

// Verify user is a participant.
$participant = db_select_one(
    'SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
    [$convId, $userId]
);

if ($participant === null) {
    json_response(['ok' => false, 'message' => 'Conversation not found or access denied.'], 403);
}

$page = max(1, (int) ($_GET['page'] ?? 1));

$countSql = 'SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND deleted_at IS NULL';
$dataSql  = 'SELECT m.*, u.display_name AS sender_name, u.avatar_url AS sender_avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_user_id
             WHERE m.conversation_id = ? AND m.deleted_at IS NULL
             ORDER BY m.created_at DESC';

$result = db_paginate($countSql, $dataSql, [$convId], $page, 50);

// Reverse the page of messages so they are in chronological (ascending) order for display.
$result['data'] = array_reverse($result['data']);

json_response(['ok' => true, 'data' => $result]);
