<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Service\ActivityLogger;


class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private JWTTokenManagerInterface $jwtManager;
    private UrlGeneratorInterface $urlGenerator;
    private ActivityLogger $activityLogger;


    public const LOGIN_ROUTE = 'app_login';


    public function __construct(UrlGeneratorInterface $urlGenerator, JWTTokenManagerInterface $jwtManager, ActivityLogger $activityLogger)
    {
        $this->jwtManager = $jwtManager;
        $this->urlGenerator = $urlGenerator;
        $this->activityLogger = $activityLogger;

    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);

        return new Passport(
            new UserBadge($data['email']),
            new PasswordCredentials($data['password'])
        );

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $token = $this->jwtManager->create($user);

        $this->activityLogger->logActivity('user_login', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'timestamp' => (new \DateTime())->format(\DateTimeInterface::ISO8601)
        ]);

        return new JsonResponse(['token' => $token], Response::HTTP_OK);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
