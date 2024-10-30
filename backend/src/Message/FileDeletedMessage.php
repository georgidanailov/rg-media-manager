<?php

namespace App\Message;

class FileDeletedMessage
{
    private int $userId;
    private ?string $fileName;

    public function __construct(int $userId, ?string $fileName = null)
    {
        $this->userId = $userId;
        $this->fileName = $fileName;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }
}
