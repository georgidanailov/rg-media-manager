<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use OpenApi\Annotations as OA;

class LoginController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Log in with credentials to obtain a JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="yourpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login, returns JWT token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", description="JWT Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or missing parameters"
     *     )
     * )
     * @Route(path="/login", name="app_login", methods={"POST"})
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse(['message' => 'Authentication processing'], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/logout",
     *     summary="Logout the user",
     *     description="This method is handled by Symfony's security system and simply logs out the current user.",
     *     @OA\Response(
     *         response=302,
     *         description="User is logged out and redirected"
     *     )
     * )
     *
     * @Route(path="/logout", name="app_logout", methods={"GET"})
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}