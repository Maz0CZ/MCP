<?php
return [
    'db_path' => __DIR__ . '/../storage/panel.sqlite',
    'base_url' => '',
    'mail' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'user@example.com',
        'password' => 'change-me',
        'encryption' => 'tls',
        'from_email' => 'no-reply@example.com',
        'from_name' => 'Zibuu MCP',
    ],
    'minecraft' => [
        'spigot_jar' => '/opt/mchost/spigot.jar',
        'servers_path' => __DIR__ . '/../storage/servers',
        'java_path' => '/usr/bin/java',
        'screen_binary' => '/usr/bin/screen',
        'start_port' => 25565,
        'log_filename' => 'console.log',
        'eula_filename' => 'eula.txt',
        'properties_template' => __DIR__ . '/../templates/server.properties.php',
        'enable_process_control' => true,
    ],
];
