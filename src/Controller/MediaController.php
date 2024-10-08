<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\User;
use App\Enum\FileType;
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
    #[Route('/media', name: 'get_all_media')]
    public function getMedia(EntityManagerInterface $em): JsonResponse
    {
        
        $media = $em->getRepository(Media::class)->findAll();
        return $this->json($media, 200 );
    }

    #[Route('/media/{id}', name: 'get_media')]
    public function getMediaById(EntityManagerInterface $em,Media $m): JsonResponse
    {
        $media = $em->getRepository(Media::class)->find($m->getId());

        if(!$media){
            throw $this->createNotFoundException('File not found');
        }

        return $this->json($media, 200 );
    }

    #[Route('/media/{id}/delete', name: 'delete_media')]
    public function deleteMedia(EntityManagerInterface $em,Media $m): JsonResponse
    {
        $media = $em->getRepository(Media::class)->find($m->getId());

        if(!$media){
            throw $this->createNotFoundException('File not found');
        }

        $em->remove($m);
        $em->flush();
        return $this->json(null, 204);
    }

    #[Route('/medias/upload', name: 'upload_media', methods: ['POST'])]
    public function uploadMedia(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse{
        $file = $request->files->get('file');
        $user = $em->getRepository(User::class)->find(1);


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
            'application/x-rar-compressed',      // RAR
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Unsupported file type'], Response::HTTP_BAD_REQUEST);
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

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads';

        $media = new Media();
        $media->setUserId($user);
        $media->setFilename($originalFilename);
        $media->setStoragePath('/'.$newFilename);
        $media->setFileSize($file->getSize());
        $media->setFileType($fileType);
        $media->setCreatedAt(new \DateTime('now'));

        if($fileType === FileType::IMAGE){
            $media->setThumbnailPath($uploadDir.'/thumbnails/'.$newFilename);
        }

        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        $em->persist($media);
        $em->flush();

        return new JsonResponse(['success' => 'file created'], Response::HTTP_OK);

    }

    #[Route('/media/{id}/upload', name: 'edit_media')]
    public function editMedia(Request $request, EntityManagerInterface $em,Media $m): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if(trim($data['name']) == ""){
            throw $this->createNotFoundException('Wrong name format');
        }

        $media = $em->getRepository(Media::class)->find($m->getId());
        if(!$media){
            throw $this->createNotFoundException('File not found');
        }

        $media->setFileName($data['name']);
        $media->setCreatedAt(new \DateTime('now'));
        $em->flush();

        return $this->json($media, 200 );
    }
}
