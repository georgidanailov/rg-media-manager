<?php

namespace App\MessageHandler;

use App\Message\ProcessMediaMessage;
use App\Service\MediaProcessingService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessMediaMessageHandler
{
    private MediaProcessingService $mediaProcessingService;

    public function __construct(MediaProcessingService $mediaProcessingService)
    {
        $this->mediaProcessingService = $mediaProcessingService;
    }

    public function __invoke(ProcessMediaMessage $message): void
    {
        $media = $message->getMedia();
        $uploadDir = $message->getUploadDir();

        $this->mediaProcessingService->handleMediaProcessing($media, $uploadDir);

    }
}
