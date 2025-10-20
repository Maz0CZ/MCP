<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\GameCatalog;
use App\Model\PackageRepository;
use App\Model\UserRepository;
use Nette\DI\Container;
use RuntimeException;

final class HomePresenter extends BasePresenter
{
    private PackageRepository $packages;
    private GameCatalog $games;

    public function __construct(PackageRepository $packages, GameCatalog $games, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->packages = $packages;
        $this->games = $games;
    }

    public function actionDefault(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    public function renderDefault(): void
    {
        $packages = [];
        foreach ($this->packages->getAll() as $package) {
            $label = (string) $package['game_key'];
            try {
                $game = $this->games->get((string) $package['game_key']);
                $label = $game['title'] ?? $label;
            } catch (RuntimeException $e) {
                // leave label as-is
            }
            $packages[] = $package + ['game_label' => $label];
        }

        $this->template->packages = $packages;
    }
}
