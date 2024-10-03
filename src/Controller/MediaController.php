<?php

namespace App\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
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

    #[Route('/media/upload', name: 'upload_media')]
    public function uploadMedia(Request $request, EntityManagerInterface $em): JsonResponse{
        $data = json_decode($request->getContent(), true);

         if(!$data){
             throw $this->createNotFoundException('No data');
         }

         $media = new Media();
         $media->setFileName($data['name']);
         $media->setCreatedAt(new \DateTime('now'));
         $media->setFileSize($data['size']);
         $media->setFileType($data['type']);
         $media->setStoragePath($data['path']);
         $media->setThumbnailPath($data['thumbnail_path']);

         $em->persist($media);
         $em->flush();

         return $this->json($media, 200 );

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
