<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\GameCatalog;
use App\Model\PackageRepository;
use App\Model\ServerRepository;
use App\Model\UserRepository;
use Nette\DI\Container;

final class AdminPresenter extends BasePresenter
{
    private ServerRepository $servers;
    private PackageRepository $packages;
    private GameCatalog $games;

    public function __construct(ServerRepository $servers, PackageRepository $packages, GameCatalog $games, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->servers = $servers;
        $this->packages = $packages;
        $this->games = $games;
    }

    protected function startup(): void
    {
        parent::startup();
        $this->requireLogin();
        $this->requireAdmin();
    }

    public function renderDefault(): void
    {
        $this->template->users = $this->users->getAll();
        $this->template->servers = $this->servers->getAll();
        $this->template->packages = $this->packages->getAll();
        $this->template->games = $this->games->all();
    }
}
