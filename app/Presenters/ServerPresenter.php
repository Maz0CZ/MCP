<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\ConsoleStreamer;
use App\Model\ServerManager;
use App\Model\ServerRepository;
use App\Model\UserRepository;
use Nette;
use Nette\DI\Container;
use Nette\Utils\Strings;

final class ServerPresenter extends BasePresenter
{
    private ServerRepository $servers;
    private ServerManager $manager;
    private ConsoleStreamer $streamer;

    public function __construct(ServerRepository $servers, ServerManager $manager, ConsoleStreamer $streamer, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->servers = $servers;
        $this->manager = $manager;
        $this->streamer = $streamer;
    }

    protected function startup(): void
    {
        parent::startup();
        $this->requireLogin();
    }

    public function renderDetail(int $id): void
    {
        $server = $this->authorizeServer($id);
        $this->template->server = $server;
    }

    public function actionConsole(int $id): void
    {
        $server = $this->authorizeServer($id);
        $lines = $this->streamer->readLines($server['directory']);
        $this->sendJson(['lines' => $lines]);
    }

    public function actionCommand(int $id): void
    {
        $server = $this->authorizeServer($id);
        $command = (string) $this->getHttpRequest()->getPost('command', '');
        if ($command !== '') {
            $this->manager->sendCommand($server, Strings::trim($command));
        }

        $this->sendJson(['status' => 'ok']);
    }

    public function actionAction(int $id): void
    {
        $server = $this->authorizeServer($id);
        $action = (string) $this->getHttpRequest()->getPost('action', '');

        switch ($action) {
            case 'start':
                $this->manager->start($server);
                break;
            case 'stop':
                $this->manager->stop($server);
                break;
            case 'restart':
                $this->manager->restart($server);
                break;
        }

        $this->sendJson(['status' => 'ok']);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorizeServer(int $id): array
    {
        $server = $this->servers->findById($id);
        if (!$server) {
            $this->error('Server not found');
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            $this->redirect('Sign:in');
        }

        if ((int) $user['id'] !== (int) $server['user_id'] && !(bool) $user['is_admin']) {
            $this->error('Forbidden', Nette\Http\IResponse::S403_FORBIDDEN);
        }

        return $server;
    }
}
