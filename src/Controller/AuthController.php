<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\JWTService;
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
}
