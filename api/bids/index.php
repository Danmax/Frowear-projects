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

$user   = fw_require_auth();
$userId = $user['id'];

// ── GET: list bids ───────────────────────────────────────────────────────────

if ($method === 'GET') {
    $tab = $_GET['tab'] ?? 'received';

    if ($tab === 'sent') {
        $bids = db_select(
            'SELECT b.*, u.display_name AS target_owner_name
             FROM bids b
             LEFT JOIN users u ON 1 = 1
             WHERE b.bidder_user_id = ?
             ORDER BY b.created_at DESC',
            [$userId]
        );
        json_response(['ok' => true, 'data' => $bids]);
    }

    if ($tab === 'contracts') {
        $contracts = db_select(
            'SELECT c.*,
                    uc.display_name AS client_name,
                    ux.display_name AS contractor_name
             FROM contracts c
             JOIN users uc ON uc.id = c.client_user_id
             JOIN users ux ON ux.id = c.contractor_user_id
             WHERE c.client_user_id = ? OR c.contractor_user_id = ?
             ORDER BY c.created_at DESC',
            [$userId, $userId]
        );
        json_response(['ok' => true, 'data' => $contracts]);
    }

    // Default: received bids.
    $bids = db_select(
        'SELECT b.*, u.display_name AS bidder_name, u.avatar_url AS bidder_avatar
         FROM bids b
         JOIN users u ON u.id = b.bidder_user_id
         ORDER BY b.created_at DESC',
        []
    );
    json_response(['ok' => true, 'data' => $bids]);
}

// ── POST: place a bid ────────────────────────────────────────────────────────

$body             = request_json();
$targetType       = trim((string) ($body['target_type'] ?? ''));
$targetId         = (int) ($body['target_id'] ?? 0);
$proposedRate     = isset($body['proposed_rate']) ? (float) $body['proposed_rate'] : null;
$rateUnit         = isset($body['rate_unit']) ? trim((string) $body['rate_unit']) : null;
$proposedTimeline = isset($body['proposed_timeline']) ? trim((string) $body['proposed_timeline']) : null;
$coverNote        = isset($body['cover_note']) ? trim((string) $body['cover_note']) : null;

$allowedTargetTypes = ['project', 'opportunity'];
if (!in_array($targetType, $allowedTargetTypes, true)) {
    json_response(['ok' => false, 'message' => 'target_type must be project or opportunity.'], 422);
}

if ($targetId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid target_id is required.'], 422);
}

try {
    $bidId = db_insert(
        'INSERT INTO bids (bidder_user_id, target_type, target_id, proposed_rate, rate_unit, proposed_timeline, cover_note)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$userId, $targetType, $targetId, $proposedRate, $rateUnit, $proposedTimeline, $coverNote]
    );
} catch (Throwable $e) {
    // SQLSTATE 23000 = Integrity constraint violation (UNIQUE duplicate)
    if (str_contains($e->getMessage(), '23000') || ($e->getCode() === '23000')) {
        json_response(['ok' => false, 'message' => 'You have already bid on this.'], 409);
    }
    json_response(['ok' => false, 'message' => 'Failed to place bid.'], 500);
}

// Attempt to notify the target owner.
try {
    if ($targetType === 'opportunity') {
        $target = db_select_one(
            'SELECT o.title, c.owner_user_id
             FROM opportunities o
             JOIN companies c ON c.id = o.company_id
             WHERE o.id = ?',
            [$targetId]
        );
    } else {
        $target = db_select_one(
            'SELECT p.title, c.owner_user_id
             FROM projects p
             JOIN companies c ON c.id = p.company_id
             WHERE p.id = ?',
            [$targetId]
        );
    }

    if ($target !== null && !empty($target['owner_user_id'])) {
        $targetOwnerId = (int) $target['owner_user_id'];
        $targetTitle   = (string) ($target['title'] ?? 'your listing');
        fw_notify(
            $targetOwnerId,
            'new_bid',
            ($user['display_name'] ?? 'Someone') . ' placed a bid on ' . $targetTitle,
            (string) ($coverNote !== null ? substr($coverNote, 0, 120) : ''),
            $userId,
            $targetType,
            $targetId
        );
    }
} catch (Throwable) {
    // Non-fatal.
}

$bid = db_select_one('SELECT * FROM bids WHERE id = ?', [$bidId]);

json_response(['ok' => true, 'bid' => $bid], 201);
