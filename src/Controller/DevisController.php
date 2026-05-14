<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Repository\ClientRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class DevisController extends AbstractController
{
     public function __construct(
        private EntityManagerInterface $entityManager,
        private DevisRepository $devisRepository,
        private ClientRepository $clientRepository,
    ) {}

 
    #[Route('/admin/devis', name: 'app_devis_list', methods: ['GET'])]
    public function devis(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $devisList = $this->devisRepository->sortStatusDevis($status);
        $data = [];
        foreach ($devisList as $devis) {
            $data[] = [
                'id' => $devis->getId(),
                'client' => [
                    'id' => $devis->getClient()->getId(),
                    'nom' => $devis->getClient()->getNom(),
                    'prenom' => $devis->getClient()->getPrenom(),
                ],
               ' dateDevis' => $devis->getDateDevis(),
                'montantTotal' => $devis->getMontantTotal(),
                'status' => $devis->getStatus(),
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/admin/devis/{id}', name: 'app_devis_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function devisById(int $id): JsonResponse
    {
        $devis = $this->devisRepository->find($id);
        if (!$devis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Devis non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $devis->getId(),
            'client' => [
                'id' => $devis->getClient()->getId(),
                'nom' => $devis->getClient()->getNom(),
                'prenom' => $devis->getClient()->getPrenom(),
            ],
            'dateDevis' => $devis->getDateDevis(),
            'montantTotal' => $devis->getMontantTotal(),
            'status' => $devis->getStatus(),
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    //add
    #[Route('/admin/devis', name: 'app_devis_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['clientId', 'dateDevis', 'montantTotal', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST); 
            }
        }  
        $client = $this->clientRepository->find($data['clientId']);
        if (!$client) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Client non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $devis = new Devis();
        $devis->setClient($client);
        $devis->setDateDevis(new \DateTimeImmutable($data['dateDevis']));
        $devis->setMontantTotal($data['montantTotal']);
        $devis->setStatus($data['status']);
        $this->entityManager->persist($devis);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Devis créé avec succès',
            'devis' => [
                'id' => $devis->getId(),
                'client' => [
                    'id' => $devis->getClient()->getId(),
                    'nom' => $devis->getClient()->getNom(),
                    'prenom' => $devis->getClient()->getPrenom(),
                ],
                'dateDevis' => $devis->getDateDevis(),
                'montantTotal' => $devis->getMontantTotal(),
                'status' => $devis->getStatus(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }
 
    //status en annule
    #[Route('/admin/devis/annuler/{id}', name: 'app_devis_annuler', methods: ['PUT'])]
    public function annulerDevis(int $id): JsonResponse
    {
        $devis = $this->devisRepository->find($id);
        if (!$devis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Devis non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $devis->setStatus('annule');
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true, 
            'message' => 'Devis annulé avec succès',
            'devis' => [
                'id' => $devis->getId(),
                'status' => $devis->getStatus(),
            ]
        ], JsonResponse::HTTP_OK);  
    }

    #[Route('/admin/devis/{id}', name: 'app_devis_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $devis = $this->devisRepository->find($id);
        if (!$devis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Devis non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['dateDevis'])) {
            $devis->setDateDevis(new \DateTimeImmutable($data['dateDevis']));
        }
        if (isset($data['montantTotal'])) {
            $devis->setMontantTotal($data['montantTotal']);
        }
        if (!empty($data['status'])) {
            $devis->setStatus($data['status']);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Devis mis à jour avec succès',
            'devis' => [
                'id' => $devis->getId(),
                'dateDevis' => $devis->getDateDevis(),
                'montantTotal' => $devis->getMontantTotal(),
                'status' => $devis->getStatus(),
            ]
        ], JsonResponse::HTTP_OK);
    }
    
}
