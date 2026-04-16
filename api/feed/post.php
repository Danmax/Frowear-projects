<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user = fw_require_auth();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    json_response(['ok' => false, 'message' => 'A valid post id is required.'], 422);
}

$post = db_select_one(
    'SELECT id, author_user_id, deleted_at FROM feed_posts WHERE id = ?',
    [$id]
);

if ($post === null || !empty($post['deleted_at'])) {
    json_response(['ok' => false, 'message' => 'Post not found.'], 404);
}

$isAdmin = ($user['role'] ?? '') === 'admin';

if ((int) $post['author_user_id'] !== $user['id'] && !$isAdmin) {
    json_response(['ok' => false, 'message' => 'You do not have permission to delete this post.'], 403);
}

db_execute(
    'UPDATE feed_posts SET deleted_at = NOW() WHERE id = ?',
    [$id]
);

json_response(['ok' => true, 'data' => null]);
