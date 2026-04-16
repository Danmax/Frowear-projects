<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$body   = request_json();
$bidId  = (int) ($body['bid_id'] ?? 0);
$status = trim((string) ($body['status'] ?? ''));

if ($bidId <= 0) {
    json_response(['ok' => false, 'message' => 'A valid bid_id is required.'], 422);
}

$allowedStatuses = ['shortlisted', 'accepted', 'declined', 'withdrawn'];
if (!in_array($status, $allowedStatuses, true)) {
    json_response(['ok' => false, 'message' => 'status must be one of: shortlisted, accepted, declined, withdrawn.'], 422);
}

$bid = db_select_one('SELECT * FROM bids WHERE id = ?', [$bidId]);

if ($bid === null) {
    json_response(['ok' => false, 'message' => 'Bid not found.'], 404);
}

// 'withdrawn' can only be done by the bidder.
if ($status === 'withdrawn' && (int) $bid['bidder_user_id'] !== $userId) {
    json_response(['ok' => false, 'message' => 'Only the bidder can withdraw a bid.'], 403);
}

db_execute(
    'UPDATE bids SET status = ?, updated_at = NOW() WHERE id = ?',
    [$status, $bidId]
);

// If accepted, create a contract draft and notify the bidder.
if ($status === 'accepted') {
    try {
        db_insert(
            'INSERT INTO contracts (bid_id, client_user_id, contractor_user_id, title, status) VALUES (?, ?, ?, ?, ?)',
            [
                $bidId,
                $userId,
                (int) $bid['bidder_user_id'],
                'Contract for bid #' . $bidId,
                'draft',
            ]
        );
    } catch (Throwable) {
        // Contract creation failure is non-fatal for the bid update itself.
    }

    try {
        fw_notify(
            (int) $bid['bidder_user_id'],
            'bid_accepted',
            'Your bid was accepted',
            'Your bid #' . $bidId . ' has been accepted. A contract draft has been created.',
            $userId,
            'bid',
            $bidId
        );
    } catch (Throwable) {
        // Non-fatal.
    }
}

if ($status === 'declined') {
    try {
        fw_notify(
            (int) $bid['bidder_user_id'],
            'bid_declined',
            'Your bid was not selected',
            'Your bid #' . $bidId . ' was not selected for this opportunity.',
            $userId,
            'bid',
            $bidId
        );
    } catch (Throwable) {
        // Non-fatal.
    }
}

$updatedBid = db_select_one('SELECT * FROM bids WHERE id = ?', [$bidId]);

json_response(['ok' => true, 'data' => ['bid' => $updatedBid]]);
