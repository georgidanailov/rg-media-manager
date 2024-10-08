<?php
namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TokenController extends AbstractController
{
    #[Route('/api/token', 'api_token', methods: ['POST'])]
    public function getToken(UserInterface $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $token = $jwtManager->create($user);
        return new JsonResponse(['token' => $token, Response::HTTP_OK]);
    }
}
