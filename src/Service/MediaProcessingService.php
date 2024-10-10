<?php

namespace App\Service;

use App\Entity\Media;
use App\Enum\FileType;

class MediaProcessingService
{
    public function processMedia(Media $media, string $uploadDir): void
    {
        $filePath = $uploadDir . $media->getStoragePath();

        if ($media->getFileType() === FileType::IMAGE) {
            $this->createImageThumbnails($filePath, $uploadDir . '/thumbnails/');
        } elseif ($media->getFileType() === FileType::VIDEO) {
            $this->createVideoThumbnail($filePath, $uploadDir . '/thumbnails/');
            $this->createVideoPreview($filePath, $uploadDir . '/previews/');
        }
    }

    private function createImageThumbnails(string $imagePath, string $thumbnailDir): void
    {
        $imageType = exif_imagetype($imagePath);
        $imageResource = null;

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $imageResource = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $imageResource = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $imageResource = imagecreatefromgif($imagePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        $sizes = [
            ['width' => 200, 'height' => 200],
            ['width' => 400, 'height' => 400]
        ];

        foreach ($sizes as $size) {
            $thumbResized = imagescale($imageResource, $size['width'], $size['height']);
            $thumbPath = $thumbnailDir . pathinfo($imagePath, PATHINFO_FILENAME) . "_{$size['width']}x{$size['height']}.jpg";
            imagejpeg($thumbResized, $thumbPath);
            imagedestroy($thumbResized);
        }

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
