<?php

namespace App\Message;

use App\Entity\Media;

class VersionUploadMessage
{
    private Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function getId(): ?int
    {
        return $this->media->getUser()->getId();
    }
}
