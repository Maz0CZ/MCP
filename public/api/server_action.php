<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/../../includes/server_manager.php';
require_login();

$user = current_user();
$serverId = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
$action = $_POST['action'] ?? '';
$server = $serverId ? find_server($serverId) : null;

if (!$server || ($server['user_id'] !== $user['id'] && !$user['is_admin'])) {
    http_response_code(404);
    exit;
}

switch ($action) {
    case 'stop':
        stop_minecraft_server($server);
        update_server_status($server['id'], 'stopping');
        break;
    case 'start':
        start_minecraft_server($server, ['ram_mb' => $server['ram_mb']]);
        update_server_status($server['id'], 'running');
        break;
    default:
        http_response_code(400);
        exit;
}

http_response_code(204);
