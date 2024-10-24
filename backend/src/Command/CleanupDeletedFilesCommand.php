<?php

// src/Command/CleanupDeletedFilesCommand.php

namespace App\Command;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name:"app:cleanup-deleted-files")]
class CleanupDeletedFilesCommand extends Command
{
    protected static $defaultName = 'app:cleanup-deleted-files';

    private $em;
    private $projectDir;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->projectDir = $parameterBag->get('kernel.project_dir');
        parent::__construct();

    }

    protected function configure()
    {
        $this->setDescription('Cleans up files that have been deleted for over 30 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime('-30 days');
        $filesToDelete = $this->em->getRepository(Media::class)->findFilesOlderThan($date);

        foreach ($filesToDelete as $file) {
            $filePath = $this->projectDir . '/public/uploads' . $file->getStoragePath();

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $this->em->flush();

        $output->writeln('Old deleted files have been cleaned up.');
        return Command::SUCCESS;
    }
}
