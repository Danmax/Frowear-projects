<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user = fw_require_auth();

$allowedPostTypes = ['update', 'opportunity', 'project', 'event', 'collaboration', 'skill_share', 'news', 'celebration', 'achievement'];
$allowedVisibility = ['public', 'connections', 'private'];

// ── GET: paginated feed ──────────────────────────────────────────────────────

if ($method === 'GET') {
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $type = $_GET['type'] ?? 'all';

    if ($type !== 'all' && !in_array($type, $allowedPostTypes, true)) {
        json_response(['ok' => false, 'message' => 'Invalid post_type filter.'], 422);
    }

    if ($type === 'all') {
        $countSql = 'SELECT COUNT(*) FROM feed_posts WHERE deleted_at IS NULL';
        $dataSql  = 'SELECT fp.*, u.display_name AS author_name, u.avatar_url AS author_avatar
                     FROM feed_posts fp
                     JOIN users u ON u.id = fp.author_user_id
                     WHERE fp.deleted_at IS NULL
                     ORDER BY fp.created_at DESC';
        $params = [];
    } else {
        $countSql = 'SELECT COUNT(*) FROM feed_posts WHERE deleted_at IS NULL AND post_type = ?';
        $dataSql  = 'SELECT fp.*, u.display_name AS author_name, u.avatar_url AS author_avatar
                     FROM feed_posts fp
                     JOIN users u ON u.id = fp.author_user_id
                     WHERE fp.deleted_at IS NULL AND fp.post_type = ?
                     ORDER BY fp.created_at DESC';
        $params = [$type];
    }

    $result = db_paginate($countSql, $dataSql, $params, $page, 20);

    json_response(['ok' => true, 'data' => $result]);
}

// ── POST: create post ────────────────────────────────────────────────────────

$body     = request_json();
$postBody = trim((string) ($body['body'] ?? ''));
$postType = trim((string) ($body['post_type'] ?? ''));
$visibility = trim((string) ($body['visibility'] ?? 'public'));
$refId    = isset($body['ref_id']) ? (int) $body['ref_id'] : null;
$refType  = isset($body['ref_type']) ? trim((string) $body['ref_type']) : null;

if ($postBody === '') {
    json_response(['ok' => false, 'message' => 'Post body is required.'], 422);
}

if (!in_array($postType, $allowedPostTypes, true)) {
    json_response(['ok' => false, 'message' => 'Invalid post_type. Must be one of: ' . implode(', ', $allowedPostTypes) . '.'], 422);
}

if (!in_array($visibility, $allowedVisibility, true)) {
    json_response(['ok' => false, 'message' => 'Invalid visibility. Must be public, connections, or private.'], 422);
}

$newId = db_insert(
    'INSERT INTO feed_posts (author_user_id, post_type, body, visibility, ref_id, ref_type) VALUES (?, ?, ?, ?, ?, ?)',
    [$user['id'], $postType, $postBody, $visibility, $refId, $refType]
);

$post = db_select_one(
    'SELECT fp.*, u.display_name AS author_name, u.avatar_url AS author_avatar
     FROM feed_posts fp
     JOIN users u ON u.id = fp.author_user_id
     WHERE fp.id = ?',
    [$newId]
);

json_response(['ok' => true, 'data' => ['post' => $post]], 201);
