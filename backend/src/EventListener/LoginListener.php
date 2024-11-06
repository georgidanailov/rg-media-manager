<?php

namespace App\EventListener;

use App\Message\LoginNotificationMessage;
use App\Service\ActivityLogger;
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
        private ActivityLogger $activityLogger
    ) {
        $this->lastLoggedAt = new \DateTime('-1 minute');
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Prevent duplicate logs by checking last logged time
        $now = new \DateTime();
        if ($this->lastLoggedAt && $now->getTimestamp() - $this->lastLoggedAt->getTimestamp() < 5) {
            return;
        }
        $this->lastLoggedAt = $now;

        $request = $event->getRequest();
        $currentIp = $request->getClientIp();
        $currentUserAgent = $request->headers->get('User-Agent');

        if ($user->getLastLoginIp() !== $currentIp || $user->getLastLoginUserAgent() !== $currentUserAgent) {
            $this->messageBus->dispatch(
                new LoginNotificationMessage($user->getEmail(), $currentIp, $currentUserAgent, $user)
            );
        }

        // Log the login activity
        $this->activityLogger->logActivity('user_login', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'ip_address' => $currentIp,
            'user_agent' => $currentUserAgent,
            'timestamp' => $now->format('Y-m-d H:i:s')
        ]);
    }


}