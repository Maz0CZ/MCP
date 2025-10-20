<?php
require_once __DIR__ . '/../includes/database.php';
$db = get_db();

$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_admin INTEGER NOT NULL DEFAULT 0
)');

$db->exec('CREATE TABLE IF NOT EXISTS packages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    ram_mb INTEGER NOT NULL,
    description TEXT
)');

$db->exec('CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    package_id INTEGER NOT NULL,
    port INTEGER NOT NULL,
    directory TEXT NOT NULL,
    status TEXT NOT NULL,
    screen_name TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(package_id) REFERENCES packages(id)
)');

$users = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($users == 0) {
    $db->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, :is_admin)')->execute([
        'email' => 'test@test.cz',
        'hash' => password_hash('test', PASSWORD_DEFAULT),
        'is_admin' => 0,
    ]);
    $db->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, :is_admin)')->execute([
        'email' => 'admin@test.cz',
        'hash' => password_hash('lofaska', PASSWORD_DEFAULT),
        'is_admin' => 1,
    ]);
}

$packagesCount = $db->query('SELECT COUNT(*) FROM packages')->fetchColumn();
if ($packagesCount == 0) {
    $packages = [
        ['name' => 'Grass', 'ram_mb' => 2048, 'description' => "Perfect for friends and small communities."],
        ['name' => 'Stone', 'ram_mb' => 4096, 'description' => "Double the memory for larger survival adventures."],
        ['name' => 'Obsidian', 'ram_mb' => 8192, 'description' => "High performance for ambitious projects and minigames."],
    ];
    foreach ($packages as $package) {
        $db->prepare('INSERT INTO packages (name, ram_mb, description) VALUES (:name, :ram, :description)')
            ->execute([
                'name' => $package['name'],
                'ram' => $package['ram_mb'],
                'description' => $package['description'],
            ]);
    }
}

echo "Database ready\n";
