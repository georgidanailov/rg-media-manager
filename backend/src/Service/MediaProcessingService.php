<?php

namespace App\Service;

use App\Entity\Media;
use App\Enum\FileType;

class MediaProcessingService
{
    public function handleMediaProcessing(Media $media, string $uploadDir): void
    {
        $filePath = $uploadDir . $media->getStoragePath();

        if ($media->getFileType() === FileType::IMAGE) {
            $this->createImageThumbnail($filePath, $uploadDir . '/thumbnails/');
        } elseif ($media->getFileType() === FileType::VIDEO) {
            $this->createVideoThumbnail($filePath, $uploadDir . '/thumbnails/');
            $this->createVideoPreview($filePath, $uploadDir . '/previews/');
        }
    }

    private function createImageThumbnail(string $imagePath, string $thumbnailDir): void
    {
        $imageType = exif_imagetype($imagePath);
        $imageResource = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => throw new \Exception('Unsupported image type')
        };

        // Resize the image to 300x300
        $thumbnail = imagescale($imageResource, 640, 360);

        // Save the thumbnail with the original filename in the thumbnails directory
        $thumbnailPath = $thumbnailDir . basename($imagePath);

        // Ensure the thumbnail directory exists
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0777, true);
        }

        imagejpeg($thumbnail, $thumbnailPath);
        imagedestroy($thumbnail);
        imagedestroy($imageResource);
    }

    private function createVideoThumbnail(string $videoPath, string $thumbnailDir): void
    {
        $thumbnailPath = $thumbnailDir . pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';
        $cmd = "ffmpeg -i $videoPath -ss 00:00:03.000 -vframes 1 $thumbnailPath";
        exec($cmd);
    }

    private function createVideoPreview(string $videoPath, string $previewDir): void
    {
        $previewPath = $previewDir . pathinfo($videoPath, PATHINFO_FILENAME) . '.mp4';
        $cmd = "ffmpeg -i $videoPath -vf scale=640:360 -b:v 500k -c:a copy $previewPath";
        exec($cmd);
    }
}
