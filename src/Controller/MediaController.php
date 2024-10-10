<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Metadata;
use App\Entity\User;
use App\Enum\FileType;
use App\Service\MediaProcessingService; // Import the MediaProcessingService
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaController extends AbstractController
{
    private MediaProcessingService $mediaProcessingService;

    // Inject MediaProcessingService into the controller
    public function __construct(MediaProcessingService $mediaProcessingService)
    {
        $this->mediaProcessingService = $mediaProcessingService;
    }

    #[Route('/media', name: 'get_all_media')]
    public function getMedia(EntityManagerInterface $em): JsonResponse
    {
        $media = $em->getRepository(Media::class)->findAll();
        return $this->json($media, 200);
    }

    #[Route('/media/{id}', name: 'get_media')]
    public function getMediaById(EntityManagerInterface $em, Media $m): JsonResponse
    {
        $media = $em->getRepository(Media::class)->find($m->getId());

        if (!$media) {
            throw $this->createNotFoundException('File not found');
        }

        return $this->json($media, 200);
    }

    #[Route('/media/{id}/delete', name: 'delete_media')]
    public function deleteMedia(EntityManagerInterface $em, Media $m): JsonResponse
    {
        $media = $em->getRepository(Media::class)->find($m->getId());

        if (!$media) {
            throw $this->createNotFoundException('File not found');
        }

        $em->remove($m);
        $em->flush();
        return $this->json(null, 204);
    }

    #[Route('/medias/upload', name: 'upload_media', methods: ['POST'])]
    public function uploadMedia(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('file');
        $user = $em->getRepository(User::class)->find(2);

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file provided or invalid file'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = [
            'image/gif',
            'image/jpeg',
            'image/png',
            'video/quicktime',
            'video/mpeg',
            'video/x-msvideo',
            'video/mp4',
            'video/x-ms-wmv',
            'video/x-matroska',
            'video/divx',
            'application/pdf',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-rar-compressed',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Unsupported file type'], Response::HTTP_BAD_REQUEST);
        }

        $filePath = $file->getPathname();

        $scanResult = shell_exec('clamscan ' . escapeshellarg($filePath));

        if(!strpos($scanResult, 'Infected files: 0')){
            return new JsonResponse(['error' => 'Infected files found'], Response::HTTP_BAD_REQUEST);
        }

        $fileType = match (true) {
            str_contains($file->getMimeType(), 'image') => FileType::IMAGE,
            str_contains($file->getMimeType(), 'video') => FileType::VIDEO,
            str_contains($file->getMimeType(), 'application/pdf') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/zip') => FileType::ARCHIVE,
            default => null
        };

        if (!$fileType) {
            return new JsonResponse(['error' => 'Unable to determine file type'], Response::HTTP_BAD_REQUEST);
        }

        $maxFileSize = match ($fileType) {
            FileType::IMAGE => 5000000,
            FileType::VIDEO, FileType::ARCHIVE => 50000000,
            FileType::DOCUMENT => 10000000,
        };

        if ($file->getSize() > $maxFileSize) {
            return new JsonResponse(['error' => 'File is too large'], Response::HTTP_BAD_REQUEST);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $media = new Media();
        $media->setUserId($user);
        $media->setFilename($originalFilename);
        $media->setStoragePath('/' . $newFilename);
        $media->setFileSize($file->getSize());
        $media->setFileType($fileType);
        $media->setCreatedAt(new \DateTime('now'));

        if ($fileType === FileType::IMAGE) {
            $media->setThumbnailPath($uploadDir . '/thumbnails/' . $newFilename);
        }

        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $em->persist($media);
        $em->flush();

        // Call media processing service after file upload
        $this->mediaProcessingService->processMedia($media, $uploadDir);

        $this->saveMediaMetadata($media, $em);

        return new JsonResponse(['success' => 'file created'], Response::HTTP_OK);
    }

    private function saveMediaMetadata(Media $media, EntityManagerInterface $em): void
    {
        $metadata = [];
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads' . $media->getStoragePath();

        if ($media->getFileType() == FileType::IMAGE) {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $metadata[] = [
                    'data_type' => 'resolution',
                    'value' => $imageInfo[0] . 'x' . $imageInfo[1]
                ];
            }
        }

        if ($media->getFileType() == FileType::VIDEO) {
            $ffmpegCommand = 'ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 ' . escapeshellarg($filePath);
            $resolution = exec($ffmpegCommand);
            if ($resolution) {
                $metadata[] = [
                    'data_type' => 'resolution',
                    'value' => $resolution
                ];
            }

            $durationCommand = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($filePath);
            $duration = exec($durationCommand);
            if ($duration) {
                $metadata[] = [
                    'data_type' => 'duration',
                    'value' => gmdate("H:i:s", $duration)
                ];
            }
        }

        foreach ($metadata as $meta) {
            $metadataEntity = new Metadata();
            $metadataEntity->setFileId($media);
            $metadataEntity->setDataType($meta['data_type']);
            $metadataEntity->setValue($meta['value']);
            $em->persist($metadataEntity);
        }

        $em->flush();
    }

    #[Route('/media/{id}/upload', name: 'edit_media')]
    public function editMedia(Request $request, EntityManagerInterface $em, Media $m): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (trim($data['name']) == "") {
            throw $this->createNotFoundException('Wrong name format');
        }

        $media = $em->getRepository(Media::class)->find($m->getId());
        if (!$media) {
            throw $this->createNotFoundException('File not found');
        }

        $media->setFileName($data['name']);
        $media->setCreatedAt(new \DateTime('now'));
        $em->flush();

        return $this->json($media, 200);
    }
}
