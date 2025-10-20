<?php

declare(strict_types=1);

namespace App\Model;

final class ConsoleStreamer
{
    private string $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = rtrim($storageDir, '/');
    }

    /**
     * @return string[]
     */
    public function readLines(string $serverDirectory, int $limit = 200): array
    {
        $logPath = rtrim($serverDirectory, '/') . '/console.log';
        if (!is_file($logPath)) {
            return ['Console log will appear once the server finishes booting.'];
        }

        $lines = @file($logPath, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            return [];
        }

        if (count($lines) > $limit) {
            $lines = array_slice($lines, -$limit);
        }

        return $lines;
    }
}
