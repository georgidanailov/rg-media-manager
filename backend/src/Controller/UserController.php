<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class UserController extends AbstractController
{
    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Missing authentication'], Response::HTTP_NOT_FOUND);
        }

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_MODERATOR')) {
            $users = $userRepository->findAll();

            // Return only the necessary fields (ID and username)
            $userData = array_map(function ($user) {
                return [
                    'id' => $user->getId(),
                    'username' => $user->getName(),
                ];
            }, $users);

            return new JsonResponse($userData, Response::HTTP_OK, ['Access-Control-Allow-Origin' => 'addada']);
        }
        return new JsonResponse(["message" => "Missing authentication"], Response::HTTP_FORBIDDEN);
    }
}
