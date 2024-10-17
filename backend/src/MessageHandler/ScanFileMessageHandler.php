<?php

namespace App\MessageHandler;

use App\Message\ScanFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\User;

#[AsMessageHandler]
class ScanFileMessageHandler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

        if (!strpos($scanResult, 'Infected files: 0')) {
            $user->setInfectedFileCount($user->getInfectedFileCount() + 1);

            if ($user->getInfectedFileCount() >= 3) {
                $user->setIsLocked(true);
            }

            $this->em->flush();
        }
    }
}

