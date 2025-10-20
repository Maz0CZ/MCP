<?php

declare(strict_types=1);

namespace App\Model;

use PHPMailer\PHPMailer\PHPMailer;

final class MailerFactory
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function create(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = (string) ($this->config['host'] ?? 'localhost');
        $mailer->Port = (int) ($this->config['port'] ?? 25);
        $mailer->SMTPAuth = !empty($this->config['username']);
        if (!empty($this->config['username'])) {
            $mailer->Username = (string) $this->config['username'];
            $mailer->Password = (string) ($this->config['password'] ?? '');
        }
        if (!empty($this->config['encryption'])) {
            $mailer->SMTPSecure = (string) $this->config['encryption'];
        }
        $mailer->setFrom(...$this->parseFromAddress());

        return $mailer;
    }

    /**
     * @return array{0: string, 1?: string}
     */
    private function parseFromAddress(): array
    {
        $from = (string) ($this->config['from'] ?? 'UltimatePanel <no-reply@example.com>');
        if (str_contains($from, '<')) {
            [$name, $address] = explode('<', $from, 2);
            return [trim($address, ' >'), trim($name)];
        }

        return [$from];
    }
}
