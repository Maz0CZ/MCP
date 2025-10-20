<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\AuthService;
use App\Model\MailerFactory;
use App\Model\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\ILogger;
use Throwable;

final class SignPresenter extends BasePresenter
{
    private MailerFactory $mailerFactory;
    private ILogger $logger;

    public function __construct(MailerFactory $mailerFactory, ILogger $logger, AuthService $auth, UserRepository $users, Container $container)
    {
        parent::__construct($auth, $users, $container);
        $this->mailerFactory = $mailerFactory;
        $this->logger = $logger;
    }

    public function actionIn(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    public function actionUp(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    public function actionOut(): void
    {
        $this->auth->logout();
        $this->flashMessage('You have been signed out.', 'info');
        $this->redirect('Home:default');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addText('email', 'Email')
            ->setRequired('Enter your email address.');
        $form->addPassword('password', 'Password')
            ->setRequired('Enter your password.');
        $form->addSubmit('send', 'Sign in')
            ->setHtmlAttribute('class', 'btn-primary');
        $form->onSuccess[] = function (Form $form, array $values): void {
            if (!$this->auth->login($values['email'], $values['password'])) {
                $form->addError('Invalid credentials.');
                return;
            }

            $this->redirect('Dashboard:default');
        };

        return $form;
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form();
        $form->addText('email', 'Email')
            ->setRequired('Enter your email address.')
            ->addRule(Form::EMAIL, 'Please provide a valid email address.');
        $form->addPassword('password', 'Password')
            ->setRequired('Choose a password.')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', 6);
        $form->addSubmit('send', 'Create account')
            ->setHtmlAttribute('class', 'btn-primary');
        $form->onSuccess[] = function (Form $form, array $values): void {
            $userId = $this->auth->register($values['email'], $values['password']);
            if ($userId === null) {
                $form->addError('An account with this email already exists.');
                return;
            }

            if ($this->mailerFactory->isEnabled()) {
                try {
                    $mailer = $this->mailerFactory->create();
                    $mailer->addAddress($values['email']);
                    $mailer->Subject = 'Welcome to UltimatePanel';
                    $mailer->Body = 'Your UltimatePanel account is live. Sign in to spin up your next server.';
                    $mailer->isHTML(true);
                    $mailer->send();
                } catch (Throwable $e) {
                    $this->logger->log($e->getMessage(), 'warning');
                }
            }

            $this->flashMessage('Account created. Please sign in.', 'success');
            $this->redirect('Sign:in');
        };

        return $form;
    }
}
