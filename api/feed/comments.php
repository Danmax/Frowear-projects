<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user = fw_require_auth();

// ── GET: load comments for a post ───────────────────────────────────────────

if ($method === 'GET') {
    $postId = (int) ($_GET['post_id'] ?? 0);

    if ($postId <= 0) {
        json_response(['ok' => false, 'message' => 'A valid post_id is required.'], 422);
    }

    $comments = db_select(
        'SELECT pc.*, u.display_name AS author_name, u.avatar_url AS author_avatar
         FROM post_comments pc
         JOIN users u ON u.id = pc.user_id
         WHERE pc.post_id = ? AND pc.deleted_at IS NULL
         ORDER BY pc.created_at ASC',
        [$postId]
    );

    json_response(['ok' => true, 'data' => ['comments' => $comments]]);
}

// ── POST: add a comment ──────────────────────────────────────────────────────

$body            = request_json();
$postId          = (int) ($body['post_id'] ?? 0);
$commentBody     = trim((string) ($body['body'] ?? ''));
$parentCommentId = isset($body['parent_comment_id']) ? (int) $body['parent_comment_id'] : null;

if ($postId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid post_id is required.'], 422);
}

if ($commentBody === '') {
    json_response(['ok' => false, 'message' => 'Comment body is required.'], 422);
}

// Verify post exists.
$post = db_select_one(
    'SELECT id, author_user_id FROM feed_posts WHERE id = ? AND deleted_at IS NULL',
    [$postId]
);

if ($post === null) {
    json_response(['ok' => false, 'message' => 'Post not found.'], 404);
}

// Validate parent comment if provided.
if ($parentCommentId !== null && $parentCommentId > 0) {
    $parentExists = db_select_one(
        'SELECT id FROM post_comments WHERE id = ? AND post_id = ? AND deleted_at IS NULL',
        [$parentCommentId, $postId]
    );
    if ($parentExists === null) {
        json_response(['ok' => false, 'message' => 'Parent comment not found.'], 404);
    }
} else {
    $parentCommentId = null;
}

$newId = db_insert(
    'INSERT INTO post_comments (post_id, user_id, body, parent_comment_id) VALUES (?, ?, ?, ?)',
    [$postId, $user['id'], $commentBody, $parentCommentId]
);

db_execute(
    'UPDATE feed_posts SET comment_count = comment_count + 1 WHERE id = ?',
    [$postId]
);

// Notify post author if they are not the commenter.
$authorId = (int) $post['author_user_id'];
if ($authorId !== $user['id']) {
    try {
        fw_notify(
            $authorId,
            'post_comment',
            ($user['display_name'] ?? 'Someone') . ' commented on your post',
            substr($commentBody, 0, 120),
            $user['id'],
            'post',
            $postId
        );
    } catch (Throwable) {
        // Non-fatal.
    }
}

$comment = db_select_one(
    'SELECT pc.*, u.display_name AS author_name, u.avatar_url AS author_avatar
     FROM post_comments pc
     JOIN users u ON u.id = pc.user_id
     WHERE pc.id = ?',
    [$newId]
);

json_response(['ok' => true, 'data' => ['comment' => $comment]], 201);
