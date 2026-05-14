<?php

namespace App\Controller;

use App\Repository\EquipeInterventionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class UtilisateurController extends AbstractController
{
    private Security $security;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private EquipeInterventionRepository $equipeInterventionRepository,
        Security $security
    ) {
        $this->security = $security;
    }


    #[Route('/admin/utilisateurs', name: 'app_utilisateurs', methods: ['GET', 'POST'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->utilisateurRepository->findBy(['is_active' => true]);
        $userData = [];
      
        foreach ($users as $user) {
            $equipes = array_map(static function ($equipeIntervention): array {
                return [
                    'id' => $equipeIntervention->getId(),
                    'commentaire' => $equipeIntervention->getCommentaire(),
                ];
            }, $user->getEquipesIntervention()->toArray());

            $userData[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'is_active' => $user->isActive(),
                'roles' => $user->getRoles(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'equipesIntervention' => $equipes,
            ];
        }

        return new JsonResponse($userData);
    }

    #[Route('/admin/utilisateurs/{id}', name: 'app_utilisateur', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->utilisateurRepository->find($id);
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'isActive' => $user->isActive(),
            'roles' => $user->getRoles(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'telephone' => $user->getTelephone(),
            'equipesIntervention' => array_map(static function ($equipeIntervention): array {
                return [
                    'id' => $equipeIntervention->getId(),
                    'commentaire' => $equipeIntervention->getCommentaire(),
                ];
            }, $user->getEquipesIntervention()->toArray()),
        ];
        return new JsonResponse($userData);
    }

    #[Route('/admin/utilisateurs/{id}', name: 'app_utilisateur_deactivate', methods: ['POST'])]
    public function deactivateUser(int $id): JsonResponse
    {
        $user = $this->utilisateurRepository->find($id);
        $connectedUser = $this->security->getUser();
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var \App\Entity\Utilisateur $connectedUser */
        if ($connectedUser->getId() == $user->getId()) {

            return new JsonResponse([
                'error' => true,
                'message' => 'Vous ne pouvez pas désactiver votre propre compte'
            ], JsonResponse::HTTP_FORBIDDEN);
        }


        $user->setIsActive(!$user->isActive());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès'
        ]);
    }

    //update
    #[Route('/admin/utilisateurs/{id}', name: 'app_utilisateur_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $this->utilisateurRepository->find($id);
        if (!$user) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Utilisateur non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['telephone'])) {
            $user->setTelephone($data['telephone']);
        }
        if (isset($data['roles']) && is_array($data['roles'])) {
            $roles = array_values(array_unique(array_filter(
                $data['roles'],
                static fn ($role) => is_string($role) && str_starts_with($role, 'ROLE_')
            )));

            if (!in_array('ROLE_USER', $roles, true)) {
                $roles[] = 'ROLE_USER';
            }

            $user->setRoles($roles);
        }

        if (array_key_exists('equipeInterventionIds', $data) || array_key_exists('equipe_intervention_ids', $data) || array_key_exists('equipeInterventionId', $data) || array_key_exists('equipe_intervention_id', $data)) {
            $equipeIds = $data['equipeInterventionIds'] ?? $data['equipe_intervention_ids'] ?? null;

            if (!is_array($equipeIds)) {
                $singleEquipeId = $data['equipeInterventionId'] ?? $data['equipe_intervention_id'] ?? null;
                $equipeIds = $singleEquipeId !== null && $singleEquipeId !== '' ? [$singleEquipeId] : [];
            }

            foreach ($user->getEquipesIntervention()->toArray() as $existingEquipe) {
                $user->removeEquipeIntervention($existingEquipe);
            }

            foreach ($equipeIds as $equipeId) {
                $equipe = $this->equipeInterventionRepository->find((int) $equipeId);
                if (!$equipe) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => "Équipe d'intervention avec l'id '$equipeId' non trouvée"
                    ], JsonResponse::HTTP_NOT_FOUND);
                }

                $user->addEquipeIntervention($equipe);
            }
        }

        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès'
        ], JsonResponse::HTTP_OK);


    }

}
