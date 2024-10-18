<?php

namespace App\Message;

use App\Service\MediaProcessingService;

// src/Message/ProcessMediaMessage.php

namespace App\Message;

use App\Entity\Media;

class ProcessMediaMessage
{
    private Media $media;
    private string $uploadDir;

    public function __construct(Media $media, string $uploadDir)
    {
        $this->media = $media;
        $this->uploadDir = $uploadDir;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
}
