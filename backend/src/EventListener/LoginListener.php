<?php

namespace App\EventListener;

use App\Message\LoginNotificationMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;

class LoginListener
{
    private \DateTime $lastLoggedAt;

    public function __construct(
        private MessageBusInterface $messageBus,
    ) {}

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $request = $event->getRequest();
        $currentIp = $request->getClientIp();
        $currentUserAgent = $request->headers->get('User-Agent');

        if ($user->getLastLoginIp() !== $currentIp || $user->getLastLoginUserAgent() !== $currentUserAgent) {
            $this->messageBus->dispatch(
                new LoginNotificationMessage($user->getEmail(), $currentIp, $currentUserAgent, $user)
            );
        }
    }


}