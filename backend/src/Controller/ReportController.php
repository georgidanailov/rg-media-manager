<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\MediaRepository;

class ReportController extends AbstractController
{
    private $mediaRepository;

    public function __construct(MediaRepository $mediaRepository) {
        $this->mediaRepository = $mediaRepository;
    }

    #[Route('/report/storage-per-user', name: 'storage-per-user', methods: ['GET'])]
    public function storagePerUser(): JsonResponse
    {
        $storageData = $this->mediaRepository->getTotalStoragePerUser();

        return $this->json($storageData, Response::HTTP_OK);
    }

    #[Route('/report/file-types-per-user', name: 'file-type-per-user', methods: ['GET'])]
    public function storagePerFileType(): JsonResponse
    {
        $data = $this->mediaRepository->getFileTypesPerUser();

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
