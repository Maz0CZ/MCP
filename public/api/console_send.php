<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/../../includes/server_manager.php';
require_login();

$user = current_user();
$serverId = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
$command = trim($_POST['command'] ?? '');
$server = $serverId ? find_server($serverId) : null;

if (!$command || !$server || ($server['user_id'] !== $user['id'] && !$user['is_admin'])) {
    http_response_code(400);
    exit;
}

send_console_command($server, $command);
http_response_code(204);
