<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

function fw_notify(
    int $userId,
    string $type,
    string $title,
    string $body = '',
    ?int $actorUserId = null,
    ?string $refType = null,
    ?int $refId = null,
    bool $queueEmail = false
): int {
    $emailSent = $queueEmail ? 0 : 1;

    return db_insert(
        'INSERT INTO notifications (user_id, type, title, body, actor_user_id, ref_type, ref_id, email_sent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $userId,
            $type,
            $title,
            $body,
            $actorUserId,
            $refType ?? '',
            $refId ?? 0,
            $emailSent,
        ]
    );
}

function fw_unread_count(int $userId): int
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
    );
    $stmt->execute([$userId]);

    return (int) $stmt->fetchColumn();
}

function fw_mark_notifications_read(int $userId, ?int $notificationId = null): void
{
    if ($notificationId !== null) {
        db_execute(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?',
            [$notificationId, $userId]
        );
        return;
    }

    db_execute(
        'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0',
        [$userId]
    );
}
