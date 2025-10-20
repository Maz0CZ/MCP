<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class Database
{
    private PDO $pdo;

    public function __construct(string $databaseFile)
    {
        $directory = dirname($databaseFile);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $this->pdo = new PDO('sqlite:' . $databaseFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
