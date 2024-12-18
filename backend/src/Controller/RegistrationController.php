<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ActivityLogger;

class RegistrationController extends AbstractController
{
    private ActivityLogger $activityLogger;


    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setQuota(104857600);
        $user->setUsedStorage(0);
        $user->setInfectedFileCount(0);
        $user->setLocked(false);

        $entityManager->persist($user);
        $entityManager->flush();

        // Log the registration activity
        $this->activityLogger->logActivity('user_registration', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
        ]);

        return new JsonResponse(['message' => 'User registered successfully'], 200);
    }
}
