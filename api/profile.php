<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$user   = fw_require_auth();

// ── Shared: load merged user + talent profile ─────────────────
function load_user_profile(int $userId): array
{
    $u = fw_find_user_by_id($userId);
    if ($u === null) {
        json_response(['ok' => false, 'message' => 'User not found.'], 404);
    }

    unset($u['password_hash']);

    $profile = db_select_one(
        'SELECT bio, role_title, availability, city, website_url, linkedin_url, github_url, visibility
         FROM talent_profiles WHERE user_id = ? AND deleted_at IS NULL LIMIT 1',
        [$userId]
    );

    return array_merge($u, $profile ?? []);
}

// ── GET — return current user profile ────────────────────────
if ($method === 'GET') {
    json_response(['ok' => true, 'user' => load_user_profile($user['id'])]);
}

// ── POST — update profile ─────────────────────────────────────
if ($method === 'POST') {
    $body = request_json();

    // Fields allowed to update on the users table
    $userFields  = ['display_name', 'avatar_url'];
    $userSet     = [];
    $userParams  = [];

    foreach ($userFields as $field) {
        if (array_key_exists($field, $body)) {
            $value = trim((string) ($body[$field] ?? ''));
            if ($field === 'display_name' && $value === '') {
                json_response(['ok' => false, 'message' => 'Display name cannot be empty.'], 422);
            }
            $userSet[]    = $field . ' = ?';
            $userParams[] = $value;
        }
    }

    if (!empty($userSet)) {
        $userParams[] = $user['id'];
        db_execute('UPDATE users SET ' . implode(', ', $userSet) . ' WHERE id = ?', $userParams);
    }

    // Fields allowed on talent_profiles
    $profileFields  = ['bio', 'city', 'availability', 'role_title', 'website_url', 'linkedin_url', 'github_url'];
    $profileSet     = [];
    $profileParams  = [];

    foreach ($profileFields as $field) {
        if (array_key_exists($field, $body)) {
            $profileSet[]    = $field . ' = ?';
            $profileParams[] = (string) ($body[$field] ?? '');
        }
    }

    $existing = db_select_one(
        'SELECT id FROM talent_profiles WHERE user_id = ? LIMIT 1',
        [$user['id']]
    );

    if ($existing) {
        if (!empty($profileSet)) {
            $profileParams[] = $user['id'];
            db_execute(
                'UPDATE talent_profiles SET ' . implode(', ', $profileSet) . ' WHERE user_id = ?',
                $profileParams
            );
        }
    } else {
        // Auto-create talent profile on first save
        $displayName = (string) ($body['display_name'] ?? $user['display_name'] ?? 'user');
        $baseSlug    = preg_replace('/[^a-z0-9]+/', '-', strtolower($displayName));
        $slug        = trim($baseSlug, '-') . '-' . $user['id'];

        $insertId = db_insert(
            'INSERT INTO talent_profiles (user_id, full_name, slug, email) VALUES (?, ?, ?, ?)',
            [$user['id'], $displayName, $slug, $user['email']]
        );

        if (!empty($profileSet)) {
            $profileParams[] = $user['id'];
            db_execute(
                'UPDATE talent_profiles SET ' . implode(', ', $profileSet) . ' WHERE user_id = ?',
                $profileParams
            );
        }
    }

    json_response(['ok' => true, 'user' => load_user_profile($user['id'])]);
}

json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
