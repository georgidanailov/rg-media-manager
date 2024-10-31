<?php

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Enum\NotificationType;
use App\Message\VersionUploadMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

#[AsMessageHandler]
class VersionUploadMessageHandler
{
    private EntityManagerInterface $em;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function __invoke(VersionUploadMessage $message)
    {
        $user = $this->em->getRepository(User::class)->find($message->getId());
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Version uploaded successfully')
            ->text('New version available for: ' . $message->getMedia()->getFileName());
        $this->mailer->send($email);


        $notification = (new Notification())
            ->setType(NotificationType::FILE_UPLOAD)
            ->setMessage('New version uploaded for: ' . $message->getMedia()->getFileName())
            ->setReceiver($user->getEmail())
            ->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }
}