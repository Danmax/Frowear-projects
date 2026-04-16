<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

const FW_USER_SESSION_KEY = 'fw_user_id';

function fw_create_user(string $email, string $password, string $displayName = '', string $role = 'talent'): array
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Invalid email address.');
    }

    $existing = fw_find_user_by_email($email);
    if ($existing !== null) {
        throw new RuntimeException('Email already registered.');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    if ($hash === false) {
        throw new RuntimeException('Password hashing failed.');
    }

    $id = db_insert(
        'INSERT INTO users (email, password_hash, display_name, role) VALUES (?, ?, ?, ?)',
        [$email, $hash, $displayName, $role]
    );

    $user = fw_find_user_by_id($id);
    if ($user === null) {
        throw new RuntimeException('Failed to retrieve created user.');
    }

    return $user;
}

function fw_find_user_by_email(string $email): ?array
{
    return db_select_one(
        'SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1',
        [$email]
    );
}

function fw_find_user_by_id(int $id): ?array
{
    return db_select_one(
        'SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1',
        [$id]
    );
}

function fw_verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function fw_get_session_user(): ?array
{
    $id = $_SESSION[FW_USER_SESSION_KEY] ?? null;
    if (!is_int($id) && !is_string($id)) {
        return null;
    }

    $intId = (int) $id;
    if ($intId <= 0) {
        return null;
    }

    return fw_find_user_by_id($intId);
}

function fw_start_session(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION[FW_USER_SESSION_KEY] = $userId;
}

function fw_end_session(): void
{
    unset($_SESSION[FW_USER_SESSION_KEY]);
}

function fw_require_auth(): array
{
    $user = fw_get_session_user();
    if ($user === null) {
        json_response(['ok' => false, 'message' => 'Authentication required.'], 401);
    }

    return $user;
}

function fw_generate_token(): array
{
    $raw = bin2hex(random_bytes(32));

    return [
        'raw'  => $raw,
        'hash' => hash('sha256', $raw),
    ];
}

function fw_create_verification_token(int $userId): string
{
    $token = fw_generate_token();

    db_execute(
        'DELETE FROM email_verification_tokens WHERE user_id = ?',
        [$userId]
    );

    db_insert(
        'INSERT INTO email_verification_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))',
        [$userId, $token['hash']]
    );

    return $token['raw'];
}

function fw_create_reset_token(int $userId): string
{
    $token = fw_generate_token();

    db_execute(
        'DELETE FROM password_reset_tokens WHERE user_id = ?',
        [$userId]
    );

    db_insert(
        'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
        [$userId, $token['hash']]
    );

    return $token['raw'];
}

function fw_consume_verification_token(string $rawToken): ?int
{
    $hash = hash('sha256', $rawToken);

    $row = db_select_one(
        'SELECT id, user_id FROM email_verification_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
        [$hash]
    );

    if ($row === null) {
        return null;
    }

    db_execute(
        'UPDATE email_verification_tokens SET used_at = NOW() WHERE id = ?',
        [$row['id']]
    );

    return (int) $row['user_id'];
}

function fw_consume_reset_token(string $rawToken): ?int
{
    $hash = hash('sha256', $rawToken);

    $row = db_select_one(
        'SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
        [$hash]
    );

    if ($row === null) {
        return null;
    }

    db_execute(
        'UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?',
        [$row['id']]
    );

    return (int) $row['user_id'];
}

function fw_mark_email_verified(int $userId): void
{
    db_execute(
        'UPDATE users SET email_verified = 1 WHERE id = ?',
        [$userId]
    );
}

function fw_update_password(int $userId, string $newPassword): void
{
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    if ($hash === false) {
        throw new RuntimeException('Password hashing failed.');
    }

    db_execute(
        'UPDATE users SET password_hash = ? WHERE id = ?',
        [$hash, $userId]
    );
}
