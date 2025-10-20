<?php

declare(strict_types=1);

namespace App\Model;

use RuntimeException;

final class ServerManager
{
    private ServerRepository $servers;
    private PackageRepository $packages;
    private string $storageDir;
    private string $spigotJar;
    private bool $enableProcessControl;
    private string $screenBinary;
    private string $javaBinary;

    public function __construct(ServerRepository $servers, PackageRepository $packages, string $storageDir, string $spigotJar, bool $enableProcessControl, string $screenBinary, string $javaBinary)
    {
        $this->servers = $servers;
        $this->packages = $packages;
        $this->storageDir = rtrim($storageDir, '/');
        $this->spigotJar = $spigotJar;
        $this->enableProcessControl = $enableProcessControl;
        $this->screenBinary = $screenBinary;
        $this->javaBinary = $javaBinary;
    }

    /**
     * @return array<string, mixed>
     */
    public function provision(int $userId, int $packageId): array
    {
        $package = $this->packages->findById($packageId);
        if (!$package) {
            throw new RuntimeException('Unknown package selected.');
        }

        $port = $this->servers->getNextPort();
        $directory = $this->createServerDirectory();
        $screenName = 'mcp_' . bin2hex(random_bytes(4));

        $serverId = $this->servers->create($userId, $packageId, $port, $directory, 'provisioning', $screenName);

        $this->writeServerFiles($directory, $port);
        $this->ensureSpigotJar($directory);

        $status = 'prepared';
        if ($this->enableProcessControl && is_file($directory . '/spigot.jar')) {
            $this->launch($directory, $screenName, (int) $package['ram_mb']);
            $status = 'running';
        } elseif (!is_file($directory . '/spigot.jar')) {
            $status = 'awaiting_jar';
        }

        $this->servers->updateStatus($serverId, $status);

        return $this->servers->findById($serverId) ?? [];
    }

    public function sendCommand(array $server, string $command): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $screen = $server['screen_name'] ?? null;
        if (!$screen) {
            return;
        }

        $fullCommand = sprintf('%s -S %s -p 0 -X stuff %s',
            escapeshellcmd($this->screenBinary),
            escapeshellarg((string) $screen),
            escapeshellarg($command . "\n")
        );

        $this->execInDirectory($server['directory'], $fullCommand);
    }

    public function start(array $server): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $package = $this->packages->findById((int) $server['package_id']);
        if (!$package) {
            throw new RuntimeException('Package missing for server.');
        }

        $screen = $server['screen_name'] ?: 'mcp_' . $server['id'];
        if ($screen !== $server['screen_name']) {
            $this->servers->updateScreenName((int) $server['id'], $screen);
        }

        $this->launch($server['directory'], $screen, (int) $package['ram_mb']);
        $this->servers->updateStatus((int) $server['id'], 'running');
    }

    public function stop(array $server): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $this->sendCommand($server, 'stop');
        $this->servers->updateStatus((int) $server['id'], 'stopping');
    }

    public function restart(array $server): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $this->sendCommand($server, 'stop');
        sleep(2);
        $this->start($server);
    }

    private function createServerDirectory(): string
    {
        $serversDir = $this->storageDir . '/servers';
        if (!is_dir($serversDir)) {
            mkdir($serversDir, 0775, true);
        }

        $directory = $serversDir . '/server_' . bin2hex(random_bytes(5));
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory;
    }

    private function writeServerFiles(string $directory, int $port): void
    {
        file_put_contents($directory . '/eula.txt', "eula=true\n");

        $properties = [
            'server-port' => (string) $port,
            'enable-query' => 'true',
            'enable-rcon' => 'false',
            'motd' => 'Welcome to your MCP server',
        ];

        $content = '';
        foreach ($properties as $key => $value) {
            $content .= $key . '=' . $value . "\n";
        }

        file_put_contents($directory . '/server.properties', $content);
        if (!is_file($directory . '/console.log')) {
            touch($directory . '/console.log');
        }
    }

    private function ensureSpigotJar(string $directory): void
    {
        if (!is_file($this->spigotJar)) {
            return;
        }

        $target = $directory . '/spigot.jar';
        if (!is_file($target)) {
            @copy($this->spigotJar, $target);
        }
    }

    private function launch(string $directory, string $screenName, int $ramMb): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $javaCommand = sprintf('%s -Xms%dM -Xmx%dM -jar spigot.jar nogui',
            escapeshellcmd($this->javaBinary),
            $ramMb,
            $ramMb
        );

        $launchCommand = sprintf(
            '%s -dmS %s bash -c %s',
            escapeshellcmd($this->screenBinary),
            escapeshellarg($screenName),
            escapeshellarg($javaCommand . ' >> console.log 2>&1')
        );

        $this->execInDirectory($directory, $launchCommand);
    }

    private function execInDirectory(string $directory, string $command): void
    {
        $full = sprintf('cd %s && %s', escapeshellarg($directory), $command);
        @exec($full);
    }
}
