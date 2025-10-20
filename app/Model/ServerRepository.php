<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class ServerRepository
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function create(
        int $userId,
        int $packageId,
        string $gameKey,
        int $port,
        string $directory,
        string $logPath,
        string $status,
        string $screenName
    ): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO servers (user_id, package_id, game_key, port, directory, log_path, status, screen_name) VALUES (:user_id, :package_id, :game_key, :port, :directory, :log_path, :status, :screen_name)');
        $stmt->execute([
            'user_id' => $userId,
            'package_id' => $packageId,
            'game_key' => $gameKey,
            'port' => $port,
            'directory' => $directory,
            'log_path' => $logPath,
            'status' => $status,
            'screen_name' => $screenName,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM servers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);

        return $server ?: null;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM servers WHERE user_id = :user_id ORDER BY id ASC');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT servers.*, users.email AS user_email, packages.name AS package_name, packages.game_key AS package_game_key FROM servers JOIN users ON users.id = servers.user_id JOIN packages ON packages.id = servers.package_id ORDER BY servers.id ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $serverId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET status = :status WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'id' => $serverId,
        ]);
    }

    public function updateScreenName(int $serverId, string $screenName): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET screen_name = :screen_name WHERE id = :id');
        $stmt->execute([
            'screen_name' => $screenName,
            'id' => $serverId,
        ]);
    }

    public function updateDirectory(int $serverId, string $directory): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET directory = :directory WHERE id = :id');
        $stmt->execute([
            'directory' => $directory,
            'id' => $serverId,
        ]);
    }

    public function getNextPort(): int
    {
        $stmt = $this->pdo->query('SELECT MAX(port) FROM servers');
        $max = (int) $stmt->fetchColumn();

        return $max ? $max + 1 : 25565;
    }
}
