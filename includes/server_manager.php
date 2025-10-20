<?php
require_once __DIR__ . '/database.php';

function next_available_port(): int
{
    $config = require __DIR__ . '/config.php';
    $db = get_db();
    $stmt = $db->query('SELECT MAX(port) AS max_port FROM servers');
    $row = $stmt->fetch();
    $maxPort = $row['max_port'] ?? null;
    return $maxPort ? $maxPort + 1 : $config['minecraft']['start_port'];
}

function create_server_directory(int $serverId): string
{
    $config = require __DIR__ . '/config.php';
    $path = rtrim($config['minecraft']['servers_path'], '/') . '/' . $serverId;
    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create server directory');
        }
    }
    return $path;
}

function write_server_files(array $server, array $package): void
{
    $config = require __DIR__ . '/config.php';
    $directory = $server['directory'];
    $propertiesTemplate = $config['minecraft']['properties_template'];
    if (is_file($propertiesTemplate)) {
        $properties = file_get_contents($propertiesTemplate);
        $properties = str_replace(['{{PORT}}'], [$server['port']], $properties);
        file_put_contents($directory . '/server.properties', $properties);
    } else {
        file_put_contents($directory . '/server.properties', "server-port={$server['port']}\n");
    }
    file_put_contents($directory . '/' . $config['minecraft']['eula_filename'], "eula=true\n");
}

function start_minecraft_server(array $server, array $package): void
{
    $config = require __DIR__ . '/config.php';
    if (!$config['minecraft']['enable_process_control']) {
        return;
    }
    $screen = escapeshellarg($config['minecraft']['screen_binary']);
    $sessionName = 'mcserver_' . $server['id'];
    $directory = escapeshellarg($server['directory']);
    $java = escapeshellarg($config['minecraft']['java_path']);
    $jar = escapeshellarg($config['minecraft']['spigot_jar']);
    $ram = (int)$package['ram_mb'];
    $logFile = escapeshellarg($server['directory'] . '/' . $config['minecraft']['log_filename']);

    $commands = [
        "$screen -dmS $sessionName bash -c 'cd $directory && $java -Xms{$ram}M -Xmx{$ram}M -jar $jar nogui >> $logFile 2>&1'",
    ];

    foreach ($commands as $command) {
        exec($command);
    }
}

function send_console_command(array $server, string $command): void
{
    $config = require __DIR__ . '/config.php';
    if (!$config['minecraft']['enable_process_control']) {
        return;
    }
    $screen = escapeshellarg($config['minecraft']['screen_binary']);
    $sessionName = escapeshellarg('mcserver_' . $server['id']);
    $sanitized = str_replace(["\r", "\n"], ' ', $command);
    $payload = escapeshellarg($sanitized . "\n");
    exec("$screen -S $sessionName -X stuff $payload");
}

function stop_minecraft_server(array $server): void
{
    send_console_command($server, 'stop');
}
