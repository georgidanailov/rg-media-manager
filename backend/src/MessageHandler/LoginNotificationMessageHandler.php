<?php

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Enum\NotificationType;
use App\Message\LoginNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class LoginNotificationMessageHandler
{
    private EntityManagerInterface $em;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;

    }

    public function __invoke(LoginNotificationMessage $message): void
    {

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($message->getEmail())
                ->subject('New Login Detected')
                ->text(sprintf(
                    "We detected a new login from IP: %s and Device: %s",
                    $message->getIp(),
                    $message->getUserAgent()
                ));

            $this->mailer->send($email);

            $notification = (new Notification())
                ->setType(NotificationType::DIFFERENT_DEVICE_LOGIN)
                ->setMessage(sprintf('New login detected from IP: %s and Device: %s', $message->getIp(), $message->getUserAgent()))
                ->setReceiver($message->getEmail())
                ->setCreatedAt(new \DateTime());

            $this->em->persist($notification);


            $user = $message->getUser();
            $user->setLastLoginIp($message->getIp());
            $user->setLastLoginUserAgent($message->getUserAgent());
            $this->em->flush();
    }
}
