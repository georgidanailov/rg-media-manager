<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//#[Route('/tags', name: 'tag_')]
class TagController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;

    public function __construct(EntityManagerInterface $entityManager, TagRepository $tagRepository)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
    }

    #[Route('/tags', name: 'list', methods: ['GET'])]
    public function listTags(): JsonResponse
    {
        $tags = $this->tagRepository->findAll();

        return $this->json($tags, 200, [], ['groups' => ['media_read']]);
    }

    #[Route('/tag/create', name: 'add', methods: ['POST'])]
    public function addTag(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tagName = trim($data['name'] ?? '');

        if (empty($tagName)) {
            return new JsonResponse(['error' => 'Tag name is required'], JsonResponse::HTTP_BAD_REQUEST);
        }


        $existingTag = $this->tagRepository->findOneBy(['name' => $tagName]);

        if (!$existingTag) {

            $tag = new Tag();
            $tag->setName($tagName);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            return $this->json(['success' => 'Tag created successfully'], JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse(['info' => 'Tag already exists'], JsonResponse::HTTP_OK);
    }

    #[Route('/tags/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteTag(int $id): JsonResponse
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return new JsonResponse(['error' => 'Tag not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Tag deleted successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/media/{id}/tags', name: 'add_to_media', methods: ['POST'])]
    public function addTagsToMedia(Request $request, string $id): JsonResponse
    {
        $media = $this->entityManager->getRepository(Media::class)->find($id);

        if (!$media) {
            return new JsonResponse(['error' => 'Media not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $tags = $data['tags'] ?? [];

        foreach ($tags as $tagName) {
            $normalizedTagName = strtolower($tagName);

            $tag = $this->tagRepository->findOneBy(['name' => $normalizedTagName]);

            if (!$tag) {
                $tag = new Tag();
                $tag->setName($normalizedTagName);
                $this->entityManager->persist($tag);
            }

            if (!$media->getTags()->contains($tag)) {
                $media->addTag($tag);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Tags added to media successfully'], JsonResponse::HTTP_OK);
    }


    #[Route('/media/{mediaId}/tags/{tagId}', name: 'remove_from_media', methods: ['DELETE'])]
    public function removeTagFromMedia(int $mediaId, int $tagId): JsonResponse
    {
        $media = $this->entityManager->getRepository(Media::class)->find($mediaId);
        $tag = $this->tagRepository->find($tagId);

        if (!$media) {
            return new JsonResponse(['error' => 'Media not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$tag) {
            return new JsonResponse(['error' => 'Tag not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($media->getTags()->contains($tag)) {
            $media->removeTag($tag);
            $this->entityManager->flush();
        }

        return new JsonResponse(['success' => 'Tag removed from media successfully'], JsonResponse::HTTP_OK);
    }
}

