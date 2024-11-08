<?php

namespace App\MessageHandler;

use App\Message\FileUploadMessage;
use App\Entity\Notification;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

#[AsMessageHandler]
class FileUploadedMessageHandler
{
    private EntityManagerInterface $em;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function __invoke(FileUploadMessage $message)
    {
        $user = $this->em->getRepository(User::class)->find($message->getId());
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $usedStorage = $user-> getUsedStorage(); // bytes
        $quota = $user->getQuota(); // bytes
        $remainingStorage = $quota - $usedStorage;

        $usedStorageMB = round($usedStorage / (1024 * 1024), 2);
        $remainingStorageMB = round($remainingStorage / (1024 * 1024), 2);
        $quotaMB = round($quota / (1024 * 1024), 2);

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('File Uploaded Successfully')
            ->text("Your file has been successfully uploaded.\n\n"
                . "You have used $usedStorageMB MB out of your $quotaMB MB quota.\n"
                . "You have $remainingStorageMB MB remaining.");

        $this->mailer->send($email);


        $notification = (new Notification())
            ->setType(NotificationType::FILE_UPLOAD)
            ->setMessage('Your file has been uploaded successfully.')
            ->setReceiver($user->getEmail())
            ->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }
}