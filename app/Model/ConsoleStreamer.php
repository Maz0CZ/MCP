<?php

declare(strict_types=1);

namespace App\Model;

final class ConsoleStreamer
{
    /**
     * @return string[]
     */
    public function readLines(array $server, int $limit = 200): array
    {
        $logPath = $server['log_path'] ?? null;
        if (!$logPath) {
            $logPath = rtrim((string) ($server['directory'] ?? ''), '/') . '/console.log';
        }
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
