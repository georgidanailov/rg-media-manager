<?php

namespace App\MessageHandler;

use App\Message\ProcessMediaMessage;
use App\Service\MediaProcessingService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProcessMediaMessageHandler implements MessageHandlerInterface
{
    private MediaProcessingService $mediaProcessingService;

    public function __construct(MediaProcessingService $mediaProcessingService)
    {
        $this->mediaProcessingService = $mediaProcessingService;
    }

    public function __invoke(ProcessMediaMessage $message): void
    {
        // Get media and upload directory from the message
        $media = $message->getMedia();
        $uploadDir = $message->getUploadDir();

        // Process the media asynchronously
        $this->mediaProcessingService->handleMediaProcessing($media, $uploadDir);
    }
}
