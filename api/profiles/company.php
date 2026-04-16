<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET' && $method !== 'PUT') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

// ── GET: view a company profile ──────────────────────────────────────────────

if ($method === 'GET') {
    $id   = (int) ($_GET['id'] ?? 0);
    $slug = trim($_GET['slug'] ?? '');

    if ($id <= 0 && $slug === '') {
        json_response(['ok' => false, 'message' => 'An id or slug is required.'], 422);
    }

    if ($id > 0) {
        $company = db_select_one(
            'SELECT c.*, u.display_name AS owner_name
             FROM companies c
             LEFT JOIN users u ON u.id = c.owner_user_id
             WHERE c.id = ? AND c.deleted_at IS NULL',
            [$id]
        );
    } else {
        $company = db_select_one(
            'SELECT c.*, u.display_name AS owner_name
             FROM companies c
             LEFT JOIN users u ON u.id = c.owner_user_id
             WHERE c.slug = ? AND c.deleted_at IS NULL',
            [$slug]
        );
    }

    if ($company === null) {
        json_response(['ok' => false, 'message' => 'Company not found.'], 404);
    }

    $companyId = (int) $company['id'];

    // Fetch associated skills.
    $skills = db_select(
        'SELECT s.name, s.category
         FROM company_skills cs
         JOIN skills s ON s.id = cs.skill_id
         WHERE cs.company_id = ?',
        [$companyId]
    );

    // Count open opportunities.
    $openOppsRow = db_select_one(
        'SELECT COUNT(*) AS cnt FROM opportunities WHERE company_id = ? AND status = \'open\' AND deleted_at IS NULL',
        [$companyId]
    );
    $openOpportunities = (int) ($openOppsRow['cnt'] ?? 0);

    $company['skills']             = $skills;
    $company['open_opportunities'] = $openOpportunities;

    json_response(['ok' => true, 'data' => ['company' => $company]]);
}

// ── PUT: update company ───────────────────────────────────────────────────────

$user   = fw_require_auth();
$userId = $user['id'];

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    json_response(['ok' => false, 'message' => 'A valid company id is required.'], 422);
}

$company = db_select_one(
    'SELECT * FROM companies WHERE id = ? AND deleted_at IS NULL',
    [$id]
);

if ($company === null) {
    json_response(['ok' => false, 'message' => 'Company not found.'], 404);
}

// Only the owner may update the company.
if ((int) $company['owner_user_id'] !== $userId && ($user['role'] ?? '') !== 'admin') {
    json_response(['ok' => false, 'message' => 'You do not have permission to update this company.'], 403);
}

$body = request_json();

$allowedFields = [
    'name'        => 'string',
    'bio'         => 'string',
    'industry'    => 'string',
    'location'    => 'string',
    'website_url' => 'string',
    'tagline'     => 'string',
];

$setClauses = [];
$params     = [];

foreach ($allowedFields as $field => $type) {
    if (!array_key_exists($field, $body)) {
        continue;
    }

    $value = $body[$field];
    $value = $value !== null ? trim((string) $value) : null;

    $setClauses[] = $field . ' = ?';
    $params[]     = $value !== '' ? $value : null;
}

if (empty($setClauses)) {
    json_response(['ok' => false, 'message' => 'No valid fields provided to update.'], 422);
}

$setClauses[] = 'updated_at = NOW()';
$params[]     = $id;
$params[]     = $userId;

db_execute(
    'UPDATE companies SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND owner_user_id = ?',
    $params
);

$updated = db_select_one(
    'SELECT c.*, u.display_name AS owner_name
     FROM companies c
     LEFT JOIN users u ON u.id = c.owner_user_id
     WHERE c.id = ?',
    [$id]
);

json_response(['ok' => true, 'data' => ['company' => $updated]]);
