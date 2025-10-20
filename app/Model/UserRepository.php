<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function create(string $email, string $passwordHash, bool $isAdmin = false): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, :is_admin)');
        $stmt->execute([
            'email' => $email,
            'hash' => $passwordHash,
            'is_admin' => $isAdmin ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, email, is_admin FROM users ORDER BY id ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
