<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Metadata;
use App\Entity\User;
use App\Enum\FileType;
use App\Message\FileDeletedMessage;
use App\Message\FileUploadMessage;
use App\Message\ScanFileMessage;
use App\Message\ProcessMediaMessage;
use App\Message\VersionUploadMessage;
use App\Service\MediaProcessingService;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\Clock\now;
use App\Service\ActivityLogger;


class MediaController extends AbstractController
{
    private $mediaService;
    private $security;
    private $activityLogger;

    // Inject MediaProcessingService into the controller
    public function __construct(MediaService $mediaService, Security $security, ActivityLogger $activityLogger)
    {
        $this->mediaService = $mediaService;
        $this->security = $security;
        $this->activityLogger = $activityLogger;
    }

    #[Route('/media', name: 'get_all_media', methods: ['GET'])]
    public function getMedia(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $criteria = [];

        if ($this->isGranted('ROLE_ADMIN')) {

            $media = $em->getRepository(Media::class)->findBy($criteria);
        } elseif ($this->isGranted('ROLE_MODERATOR')) {

            $media = $em->getRepository(Media::class)->findBy($criteria);
        } else {
            $criteria['user'] = $user;
            $media = $em->getRepository(Media::class)->findBy($criteria);
        }

        return $this->json($media, 200, [], ['groups' => ['media_read']]);
    }

    #[Route('/media/filter', name: 'filter_media', methods: ['GET'])]
    public function filterMedia(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $criteria = [];
        $user = $this->security->getUser();

        $mediaQuery = $em->getRepository(Media::class)
            ->createQueryBuilder('m')
            ->where('m.is_current_version = :is_current_version')
            ->andWhere('m.deleted_at IS NULL')
            ->setParameter('is_current_version', true);

// Filter by file type if provided
        if ($type = $request->query->get('type')) {
            $mediaQuery->andWhere('m.file_type = :type')
                ->setParameter('type', $type);
        }

// Filter by file name (partial match) if provided
        if ($name = $request->query->get('name')) {
            $mediaQuery->andWhere('m.file_name LIKE :name')
                ->setParameter('name', '%' . $name . '%'); // Partial match using wildcards
        }

        if ($userId = $request->query->get('user')) {
            $mediaQuery->andWhere("m.user = :user")
                ->setParameter('user',$userId);
        }

        if ($tag = $request->query->get('tag')) {
            $mediaQuery->join('m.tags', 't')
            ->andWhere('t.name = :tag')
            ->setParameter('tag', $tag);
        }

        if ($dateFilter = $request->query->get('date')) {
            $now = new \DateTime();

            switch ($dateFilter) {
                case '24hours':
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-1 day'));
                    break;
                case 'lastWeek':
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-7 days'));
                    break;
                case 'lastMonth':
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-1 month'));
                    break;
                case 'lastThreeMonths':
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-3 months'));
                    break;
                case 'lastSixMonths':
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-6 months'));
                    break;
                case "lastYear":
                    $mediaQuery->andWhere('m.created_at >= :dateLimit')
                        ->setParameter('dateLimit', (clone $now)->modify('-1 year'));
                    break;
                default:
                    break;
            }
        }

// Add more filters or criteria as needed


        $results = $mediaQuery->getQuery()->getResult();

        // Filter by file size
        if ($size = $request->query->get('size')) {
            switch ($size) {
                case 'small':
                    $mediaQuery->andWhere('m.file_size < :size')
                        ->setParameter('size', 10 * 1024 * 1024); // Less than 10 MB
                    break;
                case 'medium':
                    $mediaQuery->andWhere('m.file_size BETWEEN :minSize AND :maxSize')
                        ->setParameter('minSize', 10 * 1024 * 1024)
                        ->setParameter('maxSize', 100 * 1024 * 1024); // Between 10 MB and 100 MB
                    break;
                case 'large':
                    $mediaQuery->andWhere('m.file_size > :size')
                        ->setParameter('size', 100 * 1024 * 1024); // Greater than 100 MB
                    break;
            }
        }

        // Handle pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // Apply pagination
        $mediaQuery->setFirstResult($offset)->setMaxResults($limit);
        $mediaList = $mediaQuery->getQuery()->getResult();

        // Total items for pagination
        $totalItems = $em->getRepository(Media::class)
            ->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.is_current_version = :is_current_version')
            ->setParameter('is_current_version', true)
            ->getQuery()
            ->getSingleScalarResult();

        $catalog = array_map(fn($m) => [
            'id' => $m->getId(),
            'name' => $m->getFileName(),
            'file' => $m->getFileType(),
            'uploadedDate' => $m->getCreatedAt()->format('Y-m-d H:i:s'),
            'extension' => explode('.', $m->getStoragePath())[1],
            'size' => $m->getFileSize(),
            'fileVersions' => $m->getVersion(),
            'tags' => $m->getTags(),
            'author' => $m->getUser()->getName(),
            'preview' => $this->generatePreview($m),
            'downloadUrl' => $this->generateUrl('download_media', ['id' => $m->getId()]),
        ], $mediaList);

        return $this->json([
            'data' => $catalog,
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'itemsPerPage' => $limit,
        ], 200, [], ['groups' => ['media_read']]);
    }

    private function generatePreview(Media $media): ?string
    {
        if ($media->getFileType() === FileType::IMAGE || $media->getFileType() === FileType::VIDEO) {
            return $media->getThumbnailPath();
        }
        return null;
    }

    #[Route('/media', name: 'list_media', methods: ['GET'])]
    public function listMedia(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $mediaQuery = $em->getRepository(Media::class)
            ->createQueryBuilder('m')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $mediaList = $mediaQuery->getQuery()->getResult();
        $total = count($mediaList);

        return $this->json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'data' => $mediaList
        ]);
    }


    #[Route('/media/{id}/download', name: 'download_media', methods: ['GET'])]
    public function downloadMedia(Media $media, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$media){
            return new JsonResponse(['message'=>'File not found'], Response::HTTP_NOT_FOUND);
        }

        if ($media->getUser()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MODERATOR')) {
            return new JsonResponse(['message'=>'Access Denied'], Response::HTTP_FORBIDDEN);
        }

            $fileName = explode(".", $media->getStoragePath());
            $extension = "." . $fileName[1];

            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads' . $media->getStoragePath();
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $media->getFileName() . $extension);
            return $response;
    }

    #[Route('/media/download-multiple', name: 'download_multiple', methods: ['POST'])]
    public function downloadMultipleMedia(Request $request, EntityManagerInterface $em): Response
    {
        $fileIds = $request->getPayload()->all('files');
        $files = [];
        foreach ($fileIds as $fileId) {

            $file = $em->getRepository(Media::class)->find($fileId);
            $files[] = $file;
        }
        return $this->downloadMultipleFiles($files);
    }

    public function downloadMultipleFiles(array $files): Response
    {

        $zip = new \ZipArchive();
        $zipFileName = 'files_' . date('YmdHis') . '.zip';
        $zipFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $zipFileName;

        if($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
            return new JsonResponse(['error'=>'Unable to create ZIP file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if(file_exists($zipFilePath)) {
            return new JsonResponse(['error'=>'ZIP file does not exist'], Response::HTTP_NOT_FOUND);
        }
        foreach ($files as $file) {
            $fileName = explode(".", $file->getStoragePath());
            $extension = "." . $fileName[1];
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads' . $file->getStoragePath();
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->getFileName() . $extension);
            }
        }

        $zip->close();

        $response = new BinaryFileResponse($zipFilePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipFileName);
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }


    #[Route('/media/{id}', name: 'get_media')]
    public function getMediaById(EntityManagerInterface $em, Media $m): JsonResponse
    {
        $user = $this->getUser();
        $criteria = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $media = $em->getRepository(Media::class)->findOneBy(['id' => $m->getId()]);
        } elseif ($this->isGranted('ROLE_MODERATOR')) {

            $media = $em->getRepository(Media::class)->findOneBy(['id' => $m->getId()]);
        } else {
            if ($user->getId() === $m->getUser()->getId()) {
                $media = $em->getRepository(Media::class)->findOneBy(['id' => $m->getId()]);

            }else{
                return new JsonResponse(['error' => 'You are not authorized to access this page.'], 403);
            }
        }

        return $this->json($media, 200, [], ['groups' => ['media_read']]);
    }

    #[Route('/media/{id}/delete', name: 'delete_media', methods: ['DELETE'])]
    public function deleteMedia(EntityManagerInterface $em, Media $media,MessageBusInterface $messageBus): JsonResponse
    {
        $file = $em->getRepository(Media::class)->find($media->getId());
        if (!$file) {
            return new JsonResponse(['message' => 'Media not found'], 404);
        }

        if ($file->getUser() == $this->getUser() || $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MODERATOR')) {
            $file->setDeletedAt(new \DateTime('now'));
            $file->getUser()->setUsedStorage($file->getUser()->getUsedStorage() - $file->getFileSize());
            $em->flush();

            if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MODERATOR') ){

                $messageBus->dispatch(new FileDeletedMessage($media->getUser()->getId(), $file->getFileName()));
            }

            return new JsonResponse(['message' => 'Media deleted'], 200);
        }

       return new JsonResponse(['message' => 'You are not allowed to delete this file'], 403);
    }

    #[Route('/medias/upload', name: 'upload_media', methods: ['POST'])]
    public function uploadMedia(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, MessageBusInterface $messageBus): JsonResponse
    {
        $file = $request->files->get('file');
        $user = $this->getUser();

        $storageUser = $em->getRepository(User::class)->find($user->getId());


        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file provided or invalid file'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = [
            'image/gif',
            'image/jpeg',
            'image/png',


            'video/quicktime',          // MOV
            'video/mpeg',               // MPG
            'video/x-msvideo',          // AVI
            'video/mp4',                // MP4
            'video/x-ms-wmv',           // WMV
            'video/x-matroska',         // MKV
            'video/divx',               // DIVX


            'application/pdf',                    // PDF
            'application/msword',                 // DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // DOCX
            'application/vnd.ms-excel',    // XLS
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',        // XLSX
            'application/vnd.ms-powerpoint',   // PPT
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX


            'application/zip',                  // ZIP
            'application/x-rar-compressed', //RAR
            'application/vnd.rar', // RAR
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Unsupported file type'], Response::HTTP_BAD_REQUEST);
        }

        $filePath = $file->getPathname();

        $messageBus->dispatch(new ScanFileMessage($filePath, $this->getUser()->getId()));


        $fileType = match (true) {
            str_contains($file->getMimeType(), 'image') => FileType::IMAGE,
            str_contains($file->getMimeType(), 'video') => FileType::VIDEO,
            str_contains($file->getMimeType(), 'application/pdf') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/zip') => FileType::ARCHIVE,
            str_contains($file->getMimeType(), 'application/vnd.rar') => FileType::ARCHIVE,
            str_contains($file->getMimeType(), 'application/x-rar-compressed') => FileType::ARCHIVE,
            str_contains($file->getMimeType(), 'application/msword') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/vnd.ms-excel') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/vnd.ms-powerpoint') => FileType::DOCUMENT,
            str_contains($file->getMimeType(), 'application/vnd.openxmlformats-officedocument.presentationml.presentation') => FileType::DOCUMENT,
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

        if ($file->getSize() + $storageUser->getUsedStorage() > $storageUser->getQuota()) {
            return new JsonResponse(['error' => 'You have exceeded your storage quota'], Response::HTTP_BAD_REQUEST);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $storageUser->setUsedStorage($storageUser->getUsedStorage() + $file->getSize());
        $media = new Media();
        $media->setUser($user);
        $media->setFilename($originalFilename);
        $media->setStoragePath('/' . $newFilename);
        $media->setFileSize($file->getSize());
        $media->setFileType($fileType);
        $media->setCreatedAt(new \DateTime('now'));
        $media->setVersion(1);
        $media->setCurrentVersion(true);

        if ($fileType === FileType::IMAGE ) {
            $media->setThumbnailPath('/uploads/thumbnails/' . $newFilename);
        }elseif ($fileType === FileType::VIDEO){
            $thumbnailFilename = pathinfo($newFilename, PATHINFO_FILENAME) . '.jpg';
            $media->setThumbnailPath('/uploads/thumbnails/' . $thumbnailFilename);
        }
        else if ($fileType === FileType::VIDEO) {
            $thumbnailFilename = pathinfo($newFilename, PATHINFO_FILENAME) . '.jpg';
            $media->setThumbnailPath('/uploads/thumbnails/' . $thumbnailFilename);
        }


        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $em->persist($media);
        $em->flush();

        $messageBus->dispatch(new FileUploadMessage($media));

        // Call media processing service after file upload
        $messageBus->dispatch(new ProcessMediaMessage($media, $uploadDir));

        $this->saveMediaMetadata($media, $em);

        $this->activityLogger->logActivity('file_upload', [
            'user_id' => $user->getId(),
            'file_name' => $media->getFilename(),
            'file_size' => $media->getFileSize(),
            'file_type' => $media->getFileType()->value,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return new JsonResponse(['success' => 'file created'], Response::HTTP_OK);

    }


    private function saveMediaMetadata(Media $media, EntityManagerInterface $em): void
    {
        $metadata = [];
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . ltrim($media->getStoragePath(), "/");
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

        if ($media->getFileType() == FileType::ARCHIVE) {
            $zipFileCountCommand = 'unzip -l ' . escapeshellarg($filePath) . ' | grep -c "^[ ]*[0-9]"';
            $fileCount = exec($zipFileCountCommand);
            if ($fileCount) {
                $metadata[] = [
                    'data_type' => 'file_count',
                    'value' => $fileCount
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



    #[Route('/media/{id}/upload', name: 'edit_media', methods: ['POST'])]
    public function uploadNewMediaVersion(Request $request, EntityManagerInterface $em, Media $media, MessageBusInterface $messageBus, SluggerInterface $slugger): JsonResponse
    {
        $originalFile = $em->getRepository(Media::class)->find($media->getId());
        $user = $this->getUser();

        $storageUser = $originalFile->getUser();


        if (!$originalFile){
            return new JsonResponse(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        if($originalFile->getUser()->getId() !== $user->getId()){
            return new JsonResponse(["error" => "You are not the owner of this file"], Response::HTTP_FORBIDDEN);
        }

        $newFile = $request->files->get('file');
        if (!$newFile instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file provided or invalid file'], Response::HTTP_BAD_REQUEST);
        }
        $allowedMimeTypes = [
            'image/gif',
            'image/jpeg',
            'image/png',


            'video/quicktime',          // MOV
            'video/mpeg',               // MPG
            'video/x-msvideo',          // AVI
            'video/mp4',                // MP4
            'video/x-ms-wmv',           // WMV
            'video/x-matroska',         // MKV
            'video/divx',               // DIVX


            'application/pdf',                    // PDF
            'application/msword',                 // DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // DOCX
            'application/vnd.ms-excel',    // XLS
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',        // XLSX
            'application/vnd.ms-powerpoint',   // PPT
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX


            'application/zip',                  // ZIP
            'application/x-rar-compressed', //RAR
            'application/vnd.rar', // RAR
        ];

        if (!in_array($newFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Unsupported file type'], Response::HTTP_BAD_REQUEST);
        }

        $filePath = $newFile->getPathname();

        $messageBus->dispatch(new ScanFileMessage($filePath, $this->getUser()->getId()));

        $fileType = match (true) {
            str_contains($newFile->getMimeType(), 'image') => FileType::IMAGE,
            str_contains($newFile->getMimeType(), 'video') => FileType::VIDEO,
            str_contains($newFile->getMimeType(), 'application/pdf') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/zip') => FileType::ARCHIVE,
            str_contains($newFile->getMimeType(), 'application/vnd.rar') => FileType::ARCHIVE,
            str_contains($newFile->getMimeType(), 'application/x-rar-compressed') => FileType::ARCHIVE,
            str_contains($newFile->getMimeType(), 'application/msword') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/vnd.ms-excel') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/vnd.ms-powerpoint') => FileType::DOCUMENT,
            str_contains($newFile->getMimeType(), 'application/vnd.openxmlformats-officedocument.presentationml.presentation') => FileType::DOCUMENT,
            default => null
        };

        if (!$fileType) {
            return new JsonResponse(['error' => 'Unable to determine file type'], Response::HTTP_BAD_REQUEST);
        }

        if (!str_contains($newFile->getMimeType(), $originalFile->getFileType()->value)) {
            return new JsonResponse(['error' => 'File types do not match'], Response::HTTP_FORBIDDEN);
        }

        $maxFileSize = match ($fileType) {
            FileType::IMAGE => 5000000,
            FileType::VIDEO, FileType::ARCHIVE => 50000000,
            FileType::DOCUMENT => 10000000,

        };

        if ($newFile->getSize()+$storageUser->getUsedStorage() > $storageUser->getQuota()) {
            return new JsonResponse(['error' => 'You have exceeded your storage quota'], Response::HTTP_BAD_REQUEST);
        }



        if ($newFile->getSize() > $maxFileSize) {
            return new JsonResponse(['error' => 'File is too large'], Response::HTTP_BAD_REQUEST);
        }

        $originalFilename = pathinfo($newFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $newFile->guessExtension();


        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $storageUser->setUsedStorage($storageUser->getUsedStorage() + $newFile->getSize());
        $newVersionFile = new Media();
        $newVersionFile->setFileName($newFile->getClientOriginalName());
        $newVersionFile->setStoragePath('/' . $newFilename);
        $newVersionFile->setUser($user);
        $newVersionFile->setFileType($originalFile->getFileType());
        $newVersionFile->setCreatedAt(new \DateTime('now'));
        $newVersionFile->setFileSize($newFile->getSize());
        $newVersionFile->setVersion($originalFile->getVersion() + 1);
        $newVersionFile->setParent($originalFile->getParent());
        $newVersionFile->setCurrentVersion(true);
        if ($fileType === FileType::IMAGE || $fileType === FileType::VIDEO) {
            $newVersionFile->setThumbnailPath($uploadDir . '/thumbnails/' . $newFilename);
        }
        $originalFile->setCurrentVersion(false);
        try {
            $newFile->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $em->persist($newVersionFile);
        $em->persist($originalFile);
        $em->flush();

        $messageBus->dispatch(new VersionUploadMessage($originalFile));

        $messageBus->dispatch(new ProcessMediaMessage($newVersionFile, $uploadDir ));

        $this->saveMediaMetadata($newVersionFile, $em);

        return new JsonResponse(["message" => "File Version for " .$originalFile->getFileName() .  " uploaded successfully"], Response::HTTP_OK);
    }

}
