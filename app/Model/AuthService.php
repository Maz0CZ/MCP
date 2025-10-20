<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Http\Session;
use Nette\Security\Passwords;

final class AuthService
{
    private Session $session;
    private UserRepository $users;
    private Passwords $passwords;

    public function __construct(Database $database, UserRepository $users, Passwords $passwords, Session $session)
    {
        $this->session = $session;
        $this->session->start();
        $this->users = $users;
        $this->passwords = $passwords;
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!$this->passwords->verify($password, $user['password_hash'])) {
            return false;
        }

        $section = $this->session->getSection('mcp_auth');
        $section->userId = (int) $user['id'];

        return true;
    }

    public function register(string $email, string $password): ?int
    {
        if ($this->users->findByEmail($email)) {
            return null;
        }

        $hash = $this->passwords->hash($password);

        return $this->users->create($email, $hash, false);
    }

    public function logout(): void
    {
        $this->session->getSection('mcp_auth')->remove();
    }

    public function getUserId(): ?int
    {
        $section = $this->session->getSection('mcp_auth');

        return isset($section->userId) ? (int) $section->userId : null;
    }

    public function isLoggedIn(): bool
    {
        return $this->getUserId() !== null;
    }
}
