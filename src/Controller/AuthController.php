<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTService $jwtService,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données requises
        $requiredFields = ['email', 'password', 'nom', 'prenom', 'telephone'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Vérifier si l'email existe déjà
        $existingUser = $this->utilisateurRepository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Cet email est déjà utilisé'
            ], Response::HTTP_CONFLICT);
        }

        // Validation email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Email invalide'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation mot de passe (minimum 8 caractères)
        if (strlen($data['password']) < 8) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe doit contenir au moins 8 caractères'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Créer l'utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail($data['email']);
        $utilisateur->setNom($data['nom']);
        $utilisateur->setPrenom($data['prenom']);
        $utilisateur->setTelephone($data['telephone']);
        $utilisateur->setRoles(['ROLE_USER']);
        $utilisateur->setIsActive(true);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['password']);
        $utilisateur->setPassword($hashedPassword);

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $utilisateur->getId(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Email et mot de passe requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $utilisateur = $this->utilisateurRepository->findOneBy(['email' => $data['email']]);

        if (!$utilisateur) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Identifiants invalides'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($utilisateur, $data['password'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Identifiants invalides'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Générer le token JWT
        $token = $this->jwtService->createToken([
            'id' => $utilisateur->getId(),
            'email' => $utilisateur->getEmail(),
            'roles' => $utilisateur->getRoles()
        ]);

        return new JsonResponse([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $utilisateur->getId(),
                'email' => $utilisateur->getEmail(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'roles' => $utilisateur->getRoles()
            ]
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Non authentifié'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'roles' => $user->getRoles()
            ]
        ]);
    }
    #[Route('/verify/{token}', name: 'api_verify', methods: ['GET'])]
    public function verify(
        string $token,
        JWTService $jwtService,
        UtilisateurRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        if (!$jwtService->isValide($token) || $jwtService->isExpired($token)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le token est invalide ou a expiré'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $jwtService->getPayload($token);
        if (!$payload || !isset($payload['user_id'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Token invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $userRepository->find($payload['user_id']);
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$user->isVerified()) {
            $user->setIsVerified(true);
            $entityManager->flush();
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Email vérifié avec succès'
        ]);
    }

    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        UtilisateurRepository $userRepository,
        SendMailService $sendMailService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Email requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);

        // Ne pas révéler si l'email existe ou non (sécurité)
        if (!$user) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.'
            ]);
        }

        // Générer un token de réinitialisation
        $resetToken = bin2hex(random_bytes(32));
        // Vous pouvez stocker ce token en base de données avec une expiration
        // Pour l'instant, on utilise un token JWT
        $jwtToken = $this->jwtService->createToken([
            'user_id' => $user->getId(),
            'type' => 'password_reset'
        ]);

        // Envoyer l'email avec le lien de réinitialisation
        $sendMailService->send(
            'no-reply@extravittonclop.com',
            $user->getEmail(),
            'Réinitialisation de votre mot de passe',
            'password_reset',
            [
                'user' => $user,
                'token' => $jwtToken,
                'resetUrl' => 'https://localhost:3000/reset-password/' . $jwtToken // À adapter selon votre config
            ]
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.'
        ]);
    }

    #[Route('/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        UtilisateurRepository $userRepository,
        EntityManagerInterface $entityManager,
        JWTService $jwtService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['token']) || empty($data['newPassword']) || empty($data['confirmPassword'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Token, nouveau mot de passe et confirmation requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $data['token'];

        // Vérifier le token
        if (!$jwtService->isValide($token) || $jwtService->isExpired($token)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le token est invalide ou a expiré'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $jwtService->getPayload($token);
        if (!$payload || !isset($payload['user_id']) || ($payload['type'] ?? null) !== 'password_reset') {
            return new JsonResponse([
                'error' => true,
                'message' => 'Token invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $userRepository->find($payload['user_id']);
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que les mots de passe correspondent
        if ($data['newPassword'] !== $data['confirmPassword']) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Les mots de passe ne correspondent pas'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier la force du mot de passe
        if (strlen($data['newPassword']) < 8) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le mot de passe doit contenir au moins 8 caractères'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Hasher et sauvegarder le nouveau mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }
}
