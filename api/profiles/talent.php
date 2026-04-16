<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'PUT') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

// ── GET: view a talent profile ───────────────────────────────────────────────

if ($method === 'GET') {
    $id   = (int) ($_GET['id'] ?? 0);
    $slug = trim($_GET['slug'] ?? '');

    if ($id <= 0 && $slug === '') {
        json_response(['ok' => false, 'message' => 'An id or slug is required.'], 422);
    }

    if ($id > 0) {
        $profile = db_select_one(
            'SELECT tp.*, u.display_name, u.avatar_url AS user_avatar, u.role
             FROM talent_profiles tp
             JOIN users u ON u.id = tp.user_id
             WHERE tp.id = ? AND tp.deleted_at IS NULL',
            [$id]
        );
    } else {
        $profile = db_select_one(
            'SELECT tp.*, u.display_name, u.avatar_url AS user_avatar, u.role
             FROM talent_profiles tp
             JOIN users u ON u.id = tp.user_id
             WHERE tp.slug = ? AND tp.deleted_at IS NULL',
            [$slug]
        );
    }

    if ($profile === null) {
        json_response(['ok' => false, 'message' => 'Profile not found.'], 404);
    }

    // Check visibility.
    $visibility = $profile['visibility'] ?? 'public';

    if ($visibility === 'private') {
        // Allow access if the viewer is the owner.
        $currentUser = fw_get_session_user();
        $isOwner     = $currentUser !== null && (int) $currentUser['id'] === (int) $profile['user_id'];
        $isAdmin     = $currentUser !== null && ($currentUser['role'] ?? '') === 'admin';

        if (!$isOwner && !$isAdmin) {
            json_response(['ok' => false, 'message' => 'Profile is private.'], 403);
        }
    }

    $talentId = (int) $profile['id'];
    $authorId = (int) $profile['user_id'];

    $skills = db_select(
        'SELECT s.name, s.category
         FROM talent_skills ts
         JOIN skills s ON s.id = ts.skill_id
         WHERE ts.talent_id = ?',
        [$talentId]
    );

    $recentPosts = db_select(
        'SELECT fp.id, fp.post_type, fp.body, fp.created_at, fp.reaction_count, fp.comment_count
         FROM feed_posts fp
         WHERE fp.author_user_id = ? AND fp.deleted_at IS NULL AND fp.visibility = \'public\'
         ORDER BY fp.created_at DESC
         LIMIT 5',
        [$authorId]
    );

    $profile['skills']      = $skills;
    $profile['recent_posts'] = $recentPosts;

    json_response(['ok' => true, 'data' => ['profile' => $profile]]);
}

// ── PUT: update own talent profile ───────────────────────────────────────────

$user   = fw_require_auth();
$userId = $user['id'];

$body = request_json();

$profile = db_select_one(
    'SELECT * FROM talent_profiles WHERE user_id = ? AND deleted_at IS NULL',
    [$userId]
);

if ($profile === null) {
    json_response(['ok' => false, 'message' => 'Talent profile not found for your account.'], 404);
}

$allowedFields = [
    'bio'          => 'string',
    'role_title'   => 'string',
    'availability' => 'string',
    'city'         => 'string',
    'website_url'  => 'string',
    'linkedin_url' => 'string',
    'github_url'   => 'string',
    'visibility'   => 'string',
];

$allowedVisibility = ['public', 'connections', 'private'];

$setClauses = [];
$params     = [];

foreach ($allowedFields as $field => $type) {
    if (!array_key_exists($field, $body)) {
        continue;
    }

    $value = $body[$field];

    if ($field === 'visibility') {
        $value = trim((string) $value);
        if (!in_array($value, $allowedVisibility, true)) {
            json_response(['ok' => false, 'message' => 'visibility must be public, connections, or private.'], 422);
        }
    } else {
        $value = $value !== null ? trim((string) $value) : null;
    }

    $setClauses[] = $field . ' = ?';
    $params[]     = $value !== '' ? $value : null;
}

if (empty($setClauses)) {
    json_response(['ok' => false, 'message' => 'No valid fields provided to update.'], 422);
}

$setClauses[] = 'updated_at = NOW()';
$params[]     = $profile['id'];

db_execute(
    'UPDATE talent_profiles SET ' . implode(', ', $setClauses) . ' WHERE id = ?',
    $params
);

$updated = db_select_one(
    'SELECT tp.*, u.display_name, u.avatar_url AS user_avatar, u.role
     FROM talent_profiles tp
     JOIN users u ON u.id = tp.user_id
     WHERE tp.id = ?',
    [$profile['id']]
);

json_response(['ok' => true, 'data' => ['profile' => $updated]]);
