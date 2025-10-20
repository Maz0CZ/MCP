<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\PackageRepository;
use App\Model\UserRepository;
use Nette\DI\Container;

final class HomePresenter extends BasePresenter
{
    private PackageRepository $packages;

    public function __construct(PackageRepository $packages, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->packages = $packages;
    }

    public function actionDefault(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    public function renderDefault(): void
    {
        $this->template->packages = $this->packages->getAll();
    }
}
