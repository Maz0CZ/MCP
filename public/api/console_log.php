<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_login();

$user = current_user();
$serverId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$server = $serverId ? find_server($serverId) : null;

if (!$server || ($server['user_id'] !== $user['id'] && !$user['is_admin'])) {
    http_response_code(404);
    exit;
}

$config = require __DIR__ . '/../../includes/config.php';
$logFile = $server['directory'] . '/' . $config['minecraft']['log_filename'];
if (!is_file($logFile)) {
    echo 'Waiting for console output...';
    exit;
}

echo file_get_contents($logFile);
