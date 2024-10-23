<?php

namespace App\Service;

class MediaService
{

    public function moveFileToBin(string $filePath): bool
    {
        $binDirectory = '/var/www/symfony/public/uploads/trash';
        $fileName = basename($filePath);
        $destinationPath = $binDirectory . '/' . $fileName;

        if (file_exists($filePath)) {
            return rename($filePath, $destinationPath);
        }
        return false;
    }

}