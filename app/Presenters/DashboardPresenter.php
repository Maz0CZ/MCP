<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\GameCatalog;
use App\Model\PackageRepository;
use App\Model\ServerManager;
use App\Model\ServerRepository;
use App\Model\UserRepository;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use RuntimeException;

final class DashboardPresenter extends BasePresenter
{
    private ServerRepository $servers;
    private PackageRepository $packages;
    private ServerManager $manager;
    private GameCatalog $games;

    public function __construct(ServerRepository $servers, PackageRepository $packages, ServerManager $manager, GameCatalog $games, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->servers = $servers;
        $this->packages = $packages;
        $this->manager = $manager;
        $this->games = $games;
    }

    protected function startup(): void
    {
        parent::startup();
        $this->requireLogin();
    }

    public function renderDefault(): void
    {
        $user = $this->getCurrentUser();
        $packageList = $this->packages->getAll();
        $map = [];
        foreach ($packageList as $package) {
            $map[$package['id']] = $package + [
                'game_label' => $this->resolveGameTitle((string) $package['game_key']),
            ];
        }

        $this->template->servers = $user ? $this->servers->getByUser((int) $user['id']) : [];
        $this->template->packages = array_values($map);
        $this->template->packageMap = $map;
    }

    protected function createComponentProvisionForm(): Form
    {
        $form = new Form();
        $packages = [];
        foreach ($this->packages->getAll() as $package) {
            $gameTitle = $this->resolveGameTitle((string) $package['game_key']);
            $packages[$package['id']] = sprintf('%s [%s] – %d MB RAM', $package['name'], $gameTitle, $package['ram_mb']);
        }
        $form->addSelect('package', 'Package', $packages)
            ->setPrompt('Select package')
            ->setRequired('Choose a package to provision.');
        $form->addSubmit('create', 'Provision server')
            ->setHtmlAttribute('class', 'btn-primary');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $userId = $this->auth->getUserId();
            if (!$userId) {
                $this->redirect('Sign:in');
            }

            try {
                $server = $this->manager->provision($userId, (int) $values['package']);
                $this->flashMessage(sprintf('Server #%d is provisioning.', $server['id'] ?? 0), 'success');
            } catch (RuntimeException $e) {
                $form->addError($e->getMessage());
                return;
            }

            $this->redirect('Dashboard:default');
        };

        return $form;
    }

    private function resolveGameTitle(string $key): string
    {
        if ($key === '') {
            return 'custom';
        }

        try {
            $definition = $this->games->get($key);
        } catch (RuntimeException $e) {
            return $key;
        }

        return $definition['title'] ?? $key;
    }
}
