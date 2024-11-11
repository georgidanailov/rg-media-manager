<?php

namespace App\MessageHandler;

use App\Message\ScanFileMessage;
use App\Entity\Notification;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Service\ActivityLogger;

#[AsMessageHandler]
class ScanFileMessageHandler
{
    private $em;
    private MailerInterface $mailer;
    private ActivityLogger $activityLogger;


    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, ActivityLogger $activityLogger)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->activityLogger = $activityLogger;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ScanFileMessage $message)
    {
        $filePath = $message->getFilePath();
        $userId = $message->getUserId();

        $user = $this->em->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new \Exception('User not found.');
        }

        $scanResult = shell_exec('clamscan ' . escapeshellarg($filePath));

        if (preg_match('/Infected files:\s+0/', $scanResult) === 0) {
            $user->setInfectedFileCount($user->getInfectedFileCount() + 1);
//            unlink($filePath);
            if ($user->getInfectedFileCount() >= 3) {
                $user->setLocked(true);
            }

            $this->em->flush();

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($user->getEmail())
                ->subject('Virus Detected in Uploaded File')
                ->text('A virus was detected in a file you uploaded, and the file has been deleted.');
            $this->mailer->send($email);


            $notification = (new Notification())
                ->setType(NotificationType::VIRUS_DETECTION)
                ->setMessage('A virus was detected in your uploaded file, and it was deleted.')
                ->setReceiver($user->getEmail())
                ->setCreatedAt(new \DateTime());

            $this->em->persist($notification);
            $this->em->flush();

            $this->activityLogger->logActivity('forbidden_activity', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'reason' => 'Virus detected in uploaded file',
                'file_path' => $filePath,
                'timestamp' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
            ]);
        }
    }
}

