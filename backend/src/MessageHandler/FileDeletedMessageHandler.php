<?php

namespace App\MessageHandler;

use App\Message\FileDeletedMessage;
use App\Entity\Notification;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

#[AsMessageHandler]
class FileDeletedMessageHandler
{
    private EntityManagerInterface $em;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function __invoke(FileDeletedMessage $message)
    {
        $user = $this->em->getRepository(User::class)->find($message->getUserId());
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $fileNameMessage = $message->getFileName() ? ' File name: ' . $message->getFileName() : '';
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('File Deleted')
            ->text('Your file has been deleted!' . $fileNameMessage . '. Contact support if you need assistance.');
        $this->mailer->send($email);

        $notificationMessage = 'Your file was deleted! ' . $fileNameMessage . '.';
        $notification = (new Notification())
            ->setType(NotificationType::FILE_MODIFICATION)
            ->setMessage($notificationMessage)
            ->setReceiver($user->getEmail())
            ->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }

}
