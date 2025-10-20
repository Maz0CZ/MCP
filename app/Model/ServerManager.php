<?php

declare(strict_types=1);

namespace App\Model;

use RuntimeException;

final class ServerManager
{
    private ServerRepository $servers;
    private PackageRepository $packages;
    private GameCatalog $games;
    private string $storageDir;
    private bool $enableProcessControl;
    private string $screenBinary;

    public function __construct(
        ServerRepository $servers,
        PackageRepository $packages,
        GameCatalog $games,
        string $storageDir,
        bool $enableProcessControl,
        string $screenBinary
    ) {
        $this->servers = $servers;
        $this->packages = $packages;
        $this->games = $games;
        $this->storageDir = rtrim($storageDir, '/');
        $this->enableProcessControl = $enableProcessControl;
        $this->screenBinary = $screenBinary;
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

        $gameKey = (string) ($package['game_key'] ?? '');
        if ($gameKey === '') {
            throw new RuntimeException('Package is missing its game profile.');
        }

        $port = $this->servers->getNextPort();
        $directory = $this->createServerDirectory($gameKey);
        $screenName = 'ult_' . bin2hex(random_bytes(4));

        $context = [
            'directory' => $directory,
            'memory' => (int) $package['ram_mb'],
            'port' => $port,
            'screen' => $screenName,
            'package_name' => (string) $package['name'],
        ];

        $launch = $this->games->prepare($gameKey, $context);

        $serverId = $this->servers->create(
            $userId,
            $packageId,
            $gameKey,
            $port,
            $directory,
            $launch['log_path'],
            'prepared',
            $screenName
        );

        if ($this->enableProcessControl) {
            $this->launch($directory, $screenName, $launch['command'], $launch['log_path']);
            $this->servers->updateStatus($serverId, 'running');
        }

        return $this->servers->findById($serverId) ?? [];
    }

    public function sendCommand(array $server, string $command): void
    {
        if (!$this->enableProcessControl || trim($command) === '') {
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

        $gameKey = (string) ($server['game_key'] ?? $package['game_key'] ?? '');
        if ($gameKey === '') {
            throw new RuntimeException('Server has no game profile.');
        }

        $screen = $server['screen_name'] ?: 'ult_' . $server['id'];
        if ($screen !== $server['screen_name']) {
            $this->servers->updateScreenName((int) $server['id'], $screen);
        }

        $context = [
            'directory' => $server['directory'],
            'memory' => (int) $package['ram_mb'],
            'port' => (int) $server['port'],
            'screen' => $screen,
            'package_name' => (string) $package['name'],
        ];

        $launch = $this->games->describe($gameKey, $context, false);

        $this->launch($server['directory'], $screen, $launch['command'], $launch['log_path']);
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

    private function createServerDirectory(string $gameKey): string
    {
        $serversDir = $this->storageDir . '/servers/' . $gameKey;
        if (!is_dir($serversDir)) {
            mkdir($serversDir, 0775, true);
        }

        $directory = $serversDir . '/server_' . bin2hex(random_bytes(5));
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory;
    }

    private function launch(string $directory, string $screenName, string $command, string $logPath): void
    {
        if (!$this->enableProcessControl) {
            return;
        }

        $escapedCommand = sprintf('cd %s && %s >> %s 2>&1',
            escapeshellarg($directory),
            $command,
            escapeshellarg($logPath)
        );

        $launchCommand = sprintf(
            '%s -dmS %s bash -c %s',
            escapeshellcmd($this->screenBinary),
            escapeshellarg($screenName),
            escapeshellarg($escapedCommand)
        );

        @exec($launchCommand);
    }

    private function execInDirectory(string $directory, string $command): void
    {
        $full = sprintf('cd %s && %s', escapeshellarg($directory), $command);
        @exec($full);
    }
}
