<?php

declare(strict_types=1);

namespace App\Model;

use RuntimeException;

/**
 * Provides lightweight metadata for supported game families so the launcher can
 * stay framework-agnostic while still provisioning minimal server assets.
 */
final class GameCatalog
{
    /** @var array<string, array<string, mixed>> */
    private array $definitions;

    /**
     * @param array<string, array<string, mixed>> $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->definitions;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $key): array
    {
        if (!isset($this->definitions[$key])) {
            throw new RuntimeException(sprintf('Unknown game profile "%s".', $key));
        }

        return $this->definitions[$key];
    }

    /**
     * Prepare the directory with any templated files and bundled executables,
     * returning launch metadata for the server lifecycle.
     *
     * @param array<string, mixed> $context
     * @return array{command: string, log_path: string, executable: string}
     */
    public function prepare(string $key, array $context): array
    {
        return $this->describe($key, $context, true);
    }

    /**
     * Build the command/log metadata. When $prepare is true the filesystem is
     * mutated (files copied/written/chmod). Otherwise it simply resolves paths.
     *
     * @param array<string, mixed> $context
     * @return array{command: string, log_path: string, executable: string}
     */
    public function describe(string $key, array $context, bool $prepare = false): array
    {
        $definition = $this->get($key);
        $directory = rtrim((string) $context['directory'], '/');
        $memory = (int) ($context['memory'] ?? 1024);
        $port = (int) ($context['port'] ?? 0);
        $package = (string) ($context['package_name'] ?? '');

        $bundled = $definition['bundledExecutable'] ?? null;
        $executable = $definition['executable'] ?? ($directory . '/' . ($bundled ? basename((string) $bundled) : 'start.sh'));
        $basePlaceholders = [
            '{directory}' => $directory,
            '{memory}' => (string) $memory,
            '{port}' => (string) $port,
            '{package}' => $package,
        ];
        $executable = strtr((string) $executable, $basePlaceholders);

        $placeholders = $basePlaceholders + [
            '{executable}' => $executable,
            '{screen}' => (string) ($context['screen'] ?? ''),
        ];

        if (isset($definition['variables']) && is_array($definition['variables'])) {
            foreach ($definition['variables'] as $name => $value) {
                $placeholders['{' . $name . '}'] = (string) $value;
            }
        }

        $log = $definition['log'] ?? '{directory}/console.log';
        $logPath = strtr((string) $log, $placeholders);

        $commandTemplate = $definition['command'] ?? '{executable}';
        $command = trim(strtr((string) $commandTemplate, $placeholders));

        if ($prepare) {
            $this->prepareFilesystem($definition, $directory, $placeholders, $executable, $logPath);
        }

        return [
            'command' => $command,
            'log_path' => $logPath,
            'executable' => $executable,
        ];
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, string> $placeholders
     */
    private function prepareFilesystem(array $definition, string $directory, array $placeholders, string $executable, string $logPath): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        if (!is_file($logPath)) {
            touch($logPath);
        }

        if (!empty($definition['files']) && is_array($definition['files'])) {
            foreach ($definition['files'] as $relative => $content) {
                $target = $directory . '/' . ltrim(strtr((string) $relative, $placeholders), '/');
                $targetDir = dirname($target);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }
                file_put_contents($target, strtr((string) $content, $placeholders));
                $this->maybeMakeExecutable($target);
            }
        }

        if (!empty($definition['bundledExecutable'])) {
            $source = (string) $definition['bundledExecutable'];
            if (is_file($source) && !is_file($executable)) {
                $targetDir = dirname($executable);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }
                @copy($source, $executable);
            }
        }

        if (!is_file($executable)) {
            // Provide a placeholder shim to keep the process controllable.
            file_put_contents($executable, "#!/bin/bash\necho 'Drop your dedicated server binary here.'\n" . "sleep 5\n");
        }

        $this->maybeMakeExecutable($executable);
    }

    private function maybeMakeExecutable(string $path): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array($extension, ['sh', 'run', 'bin'], true)) {
            @chmod($path, 0755);
        }
    }
}
