<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class PackageRepository
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, ram_mb, description FROM packages ORDER BY ram_mb ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, ram_mb, description FROM packages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        return $package ?: null;
    }
}
