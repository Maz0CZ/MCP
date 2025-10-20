<?php

declare(strict_types=1);

use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$container = Bootstrap::boot()->createContainer();
/** @var App\Model\Database $database */
$database = $container->getByType(App\Model\Database::class);
$pdo = $database->getConnection();

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_admin INTEGER NOT NULL DEFAULT 0
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS packages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    game_key TEXT NOT NULL,
    ram_mb INTEGER NOT NULL,
    description TEXT
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    package_id INTEGER NOT NULL,
    game_key TEXT NOT NULL,
    port INTEGER NOT NULL,
    directory TEXT NOT NULL,
    log_path TEXT NOT NULL,
    status TEXT NOT NULL,
    screen_name TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(package_id) REFERENCES packages(id)
)');

$users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($users === 0) {
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, :is_admin)');
    $stmt->execute([
        'email' => 'test@test.cz',
        'hash' => password_hash('test', PASSWORD_DEFAULT),
        'is_admin' => 0,
    ]);
    $stmt->execute([
        'email' => 'admin@test.cz',
        'hash' => password_hash('lofaska', PASSWORD_DEFAULT),
        'is_admin' => 1,
    ]);
}

$packages = (int) $pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
if ($packages === 0) {
    $seed = [
        [
            'name' => 'Offline Mine',
            'game_key' => 'minecraft_offline',
            'ram_mb' => 2048,
            'description' => 'Ready-to-go Minecraft Java stack with offline login and bundled configs.',
        ],
        [
            'name' => 'SteamCMD Flex',
            'game_key' => 'generic_linux',
            'ram_mb' => 4096,
            'description' => 'Generic Linux package for Source/Unity dedicated servers and cracked builds.',
        ],
        [
            'name' => 'Wine Lab',
            'game_key' => 'wine_wrapper',
            'ram_mb' => 6144,
            'description' => 'Deploy Windows-only servers under Wine or Proton without hassle.',
        ],
    ];

    $stmt = $pdo->prepare('INSERT INTO packages (name, game_key, ram_mb, description) VALUES (:name, :game_key, :ram, :description)');
    foreach ($seed as $package) {
        $stmt->execute([
            'name' => $package['name'],
            'game_key' => $package['game_key'],
            'ram' => $package['ram_mb'],
            'description' => $package['description'],
        ]);
    }
}

echo "UltimatePanel database ready\n";
