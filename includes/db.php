<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

function db_select(string $sql, array $params = []): array
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return is_array($rows) ? $rows : [];
}

function db_select_one(string $sql, array $params = []): ?array
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function db_insert(string $sql, array $params = []): int
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $pdo->lastInsertId();
}

function db_execute(string $sql, array $params = []): int
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount();
}

function db_paginate(string $countSql, string $dataSql, array $params, int $page, int $perPage = 20): array
{
    $pdo = db_connection();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $page = max(1, $page);

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $pages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
    $offset = ($page - 1) * $perPage;

    $dataStmt = $pdo->prepare($dataSql . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $dataStmt->execute($params);
    $rows = $dataStmt->fetchAll();

    return [
        'data'     => is_array($rows) ? $rows : [],
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'pages'    => max(1, $pages),
    ];
}
