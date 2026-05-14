<?php

namespace App\Security;

use App\Service\JWTService;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JWTAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private JWTService $jwtService,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new CustomUserMessageAuthenticationException('Token JWT manquant');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->decodeToken($token);

        if (!$payload) {
            throw new CustomUserMessageAuthenticationException('Token JWT invalide ou expiré');
        }

        $email = $payload['email'] ?? null;
        if (!$email) {
            throw new CustomUserMessageAuthenticationException('Token JWT invalide: email manquant');
        }

        return new SelfValidatingPassport(
            new UserBadge($email, function ($userIdentifier) {
                $user = $this->utilisateurRepository->findOneBy(['email' => $userIdentifier]);
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Utilisateur non trouvé');
                }
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continue la requête
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => true,
            'message' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}
