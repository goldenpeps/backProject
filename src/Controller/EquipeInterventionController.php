<?php

namespace App\Controller;

use App\Entity\EquipeIntervention;
use App\Repository\EquipeInterventionRepository;
use App\Repository\InterventionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class EquipeInterventionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InterventionRepository $interventionRepository,
        private EquipeInterventionRepository $equipeInterventionRepository,
        private UtilisateurRepository $utilisateurRepository,

    ) {}

    //add

    #[Route('/admin/equipe-intervention', name: 'app_equipe_intervention_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }
        if (empty($data['commentaire'])) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le champ 'commentaire' est requis"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $utilisateurIds = [];
        if (!empty($data['utilisateurs']) && is_array($data['utilisateurs'])) {
            $utilisateurIds = $data['utilisateurs'];
        } elseif (!empty($data['utilisateur'])) {
            $utilisateurIds = [$data['utilisateur']];
        }

        $equipeIntervention = new EquipeIntervention();
        $equipeIntervention->setCommentaire($data['commentaire']);

        $interventionId = $data['intervention'] ?? $data['Intervention'] ?? null;
        if (!empty($interventionId)) {
            $intervention = $this->interventionRepository->find($interventionId);
            if (!$intervention) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Intervention non trouvée'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $equipeIntervention->addIntervention($intervention);
        }

        foreach ($utilisateurIds as $utilisateurId) {
            $utilisateur = $this->utilisateurRepository->find($utilisateurId);
            if (!$utilisateur) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Utilisateur avec l'id '$utilisateurId' non trouvé"
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $equipeIntervention->addUtilisateur($utilisateur);
        }

        $this->entityManager->persist($equipeIntervention);
        $this->entityManager->flush();

        $utilisateurs = array_map(function ($utilisateur) {
            return [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
            ];
        }, $equipeIntervention->getUtilisateurs()->toArray());

        return new JsonResponse([
            'success' => true,
            'message' => 'Équipe d\'intervention créée avec succès',
            'equipeIntervention' => [
                'id' => $equipeIntervention->getId(),
                'utilisateurs' => $utilisateurs,
                'commentaire' => $equipeIntervention->getCommentaire(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }


    //delete
    #[Route('/admin/equipe-intervention/{id}', name: 'app_equipe_intervention_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $equipeIntervention = $this->equipeInterventionRepository->find($id);
        if (!$equipeIntervention) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Équipe d\'intervention non trouvée'
            ], JsonResponse::HTTP_NOT_FOUND);   
        }

        foreach ($equipeIntervention->getUtilisateurs()->toArray() as $utilisateur) {
            $equipeIntervention->removeUtilisateur($utilisateur);
        }

        $this->entityManager->remove($equipeIntervention);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Équipe d\'intervention supprimée avec succès'
        ], JsonResponse::HTTP_OK);
    }

    //update
    #[Route('/admin/equipe-intervention/{id}', name: 'app_equipe_intervention_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $equipeIntervention = $this->equipeInterventionRepository->find($id);
        if (!$equipeIntervention) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Équipe d\'intervention non trouvée'
            ], JsonResponse::HTTP_NOT_FOUND);   
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }
        if (!empty($data['commentaire'])) {
            $equipeIntervention->setCommentaire($data['commentaire']);
        }
        $interventionId = $data['intervention'] ?? $data['Intervention'] ?? null;
        if ($interventionId !== null && $interventionId !== '') {
            $intervention = $this->interventionRepository->find($interventionId);
            if (!$intervention) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Intervention non trouvée'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $equipeIntervention->addIntervention($intervention);
        }
        if (isset($data['utilisateurs']) && is_array($data['utilisateurs'])) {
            foreach ($equipeIntervention->getUtilisateurs()->toArray() as $existingUtilisateur) {
                $equipeIntervention->removeUtilisateur($existingUtilisateur);
            }

            foreach ($data['utilisateurs'] as $utilisateurId) {
                $utilisateur = $this->utilisateurRepository->find($utilisateurId);
                if (!$utilisateur) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => "Utilisateur avec l'id '$utilisateurId' non trouvé"
                    ], JsonResponse::HTTP_NOT_FOUND);
                }
                $equipeIntervention->addUtilisateur($utilisateur);
            }
        } elseif (isset($data['utilisateur'])) {
            $utilisateur = $this->utilisateurRepository->find($data['utilisateur']);
            if (!$utilisateur) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Utilisateur non trouvé'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $equipeIntervention->addUtilisateur($utilisateur);
        }



        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Équipe d\'intervention mise à jour avec succès'
        ], JsonResponse::HTTP_OK);
    }

    //recherche par date , terrein , utilisateur
    #[Route('/admin/equipe-intervention/search', name: 'app_equipe_intervention_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        $terrainId = $request->query->get('terrain_id');
        $utilisateurId = $request->query->get('utilisateur_id');
        $results = $this->equipeInterventionRepository->searchByDateTerrainUtilisateur($date, $terrainId, $utilisateurId);
        $data = [];
        foreach ($results as $equipeIntervention) {
            $data[] = [
                'id' => $equipeIntervention->getId(),
                'commentaire' => $equipeIntervention->getCommentaire(),
                'interventions' => array_map(function ($intervention) {
                    return [
                        'id' => $intervention->getId(),
                        'datePrevue' => $intervention->getDatePrevue()?->format('Y-m-d'),
                        'dateRealisation' => $intervention->getDateRealisation()?->format('Y-m-d'),
                    ];
                }, $equipeIntervention->getInterventions()->toArray()),
                'utilisateurs' => array_map(function ($utilisateur) {
                    return [
                        'id' => $utilisateur->getId(),
                        'nom' => $utilisateur->getNom(),
                        'prenom' => $utilisateur->getPrenom(),
                    ];
                }, $equipeIntervention->getUtilisateurs()->toArray()),
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
    //one intervention equipe
    #[Route('/admin/equipe-intervention/{id}', name: 'app_equipe_intervention_get', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $equipeIntervention = $this->equipeInterventionRepository->find($id);
        if (!$equipeIntervention) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Équipe d\'intervention non trouvée'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $equipeIntervention->getId(),
            'commentaire' => $equipeIntervention->getCommentaire(),
            'interventions' => array_map(function ($intervention) {
                return [
                    'id' => $intervention->getId(),
                    'datePrevue' => $intervention->getDatePrevue()?->format('Y-m-d'),
                    'dateRealisation' => $intervention->getDateRealisation()?->format('Y-m-d'),
                ];
            }, $equipeIntervention->getInterventions()->toArray()),
            'utilisateurs' => array_map(function ($utilisateur) {
                return [
                    'id' => $utilisateur->getId(),
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                ];
            }, $equipeIntervention->getUtilisateurs()->toArray()),
        ];
    
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

}
