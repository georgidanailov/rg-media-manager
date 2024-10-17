<?php
namespace App\Message;

class ScanFileMessage
{
    private string $filePath;
    private int $userId;

    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}