<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\UserRepository;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class BasePresenter extends Presenter
{
    protected AuthService $auth;
    protected UserRepository $users;
    protected Container $container;

    public function __construct(AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct();
        $this->auth = $auth;
        $this->users = $users;
        $this->container = $container;
    }

    protected function startup(): void
    {
        parent::startup();
        $parameters = $this->container->getParameters();
        $this->template->brandName = $parameters['brandName'] ?? 'UltimatePanel';
        $this->template->accentColor = $parameters['accentColor'] ?? '#ab47bc';
        $this->template->user = $this->getCurrentUser();
    }

    protected function getCurrentUser(): ?array
    {
        $userId = $this->auth->getUserId();
        return $userId ? $this->users->findById($userId) : null;
    }

    protected function requireLogin(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    protected function requireAdmin(): void
    {
        $user = $this->getCurrentUser();
        if (!$user || !(bool) $user['is_admin']) {
            $this->error('Forbidden', Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
}
