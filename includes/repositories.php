<?php
require_once __DIR__ . '/database.php';

function get_packages(): array
{
    $db = get_db();
    $stmt = $db->query('SELECT * FROM packages ORDER BY ram_mb');
    return $stmt->fetchAll();
}

function get_user_servers(int $userId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT servers.*, packages.name AS package_name, packages.ram_mb FROM servers JOIN packages ON packages.id = servers.package_id WHERE user_id = :user_id ORDER BY id DESC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function find_server(int $serverId): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT servers.*, packages.name AS package_name, packages.ram_mb FROM servers JOIN packages ON packages.id = servers.package_id WHERE servers.id = :id');
    $stmt->execute(['id' => $serverId]);
    $server = $stmt->fetch();
    return $server ?: null;
}

function update_server_status(int $serverId, string $status): void
{
    $db = get_db();
    $stmt = $db->prepare('UPDATE servers SET status = :status WHERE id = :id');
    $stmt->execute(['status' => $status, 'id' => $serverId]);
}
