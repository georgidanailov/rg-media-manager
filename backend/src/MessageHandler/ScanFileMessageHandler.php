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
        if (preg_match('/Infected files:\s+0/', $scanResult) === 0) {
            $user->setInfectedFileCount($user->getInfectedFileCount() + 1);
            unlink($filePath);
            if ($user->getInfectedFileCount() >= 3) {
                $user->setLocked(true);
            }

            $this->em->flush();
        }
    }
}

