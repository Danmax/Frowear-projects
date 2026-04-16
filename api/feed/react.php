<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user = fw_require_auth();

$body     = request_json();
$postId   = (int) ($body['post_id'] ?? 0);
$reaction = trim((string) ($body['reaction'] ?? 'like'));

if ($postId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid post_id is required.'], 422);
}

$allowedReactions = ['like', 'celebrate', 'support', 'insightful', 'curious'];
if (!in_array($reaction, $allowedReactions, true)) {
    json_response(['ok' => false, 'message' => 'Invalid reaction. Must be one of: ' . implode(', ', $allowedReactions) . '.'], 422);
}

// Verify post exists.
$post = db_select_one(
    'SELECT id FROM feed_posts WHERE id = ? AND deleted_at IS NULL',
    [$postId]
);

if ($post === null) {
    json_response(['ok' => false, 'message' => 'Post not found.'], 404);
}

// Check if this user has already reacted.
$existing = db_select_one(
    'SELECT id FROM post_reactions WHERE post_id = ? AND user_id = ?',
    [$postId, $user['id']]
);

if ($existing !== null) {
    // Remove existing reaction and decrement counter.
    db_execute(
        'DELETE FROM post_reactions WHERE id = ?',
        [$existing['id']]
    );
    db_execute(
        'UPDATE feed_posts SET reaction_count = GREATEST(0, reaction_count - 1) WHERE id = ?',
        [$postId]
    );

    json_response(['ok' => true, 'data' => ['reacted' => false]]);
}

// Add reaction and increment counter.
db_insert(
    'INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)',
    [$postId, $user['id'], $reaction]
);
db_execute(
    'UPDATE feed_posts SET reaction_count = reaction_count + 1 WHERE id = ?',
    [$postId]
);

json_response(['ok' => true, 'data' => ['reacted' => true]], 201);
