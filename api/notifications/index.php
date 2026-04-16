<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$user   = fw_require_auth();
$userId = $user['id'];

$page = max(1, (int) ($_GET['page'] ?? 1));

$countSql = 'SELECT COUNT(*) FROM notifications WHERE user_id = ?';
$dataSql  = 'SELECT n.*, u.display_name AS actor_name, u.avatar_url AS actor_avatar
             FROM notifications n
             LEFT JOIN users u ON u.id = n.actor_user_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC';

$result = db_paginate($countSql, $dataSql, [$userId], $page, 30);

$result['unread_count'] = fw_unread_count($userId);

json_response(['ok' => true, 'data' => $result]);
