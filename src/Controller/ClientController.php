<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api')]

final class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository
    ) {}

    #[Route('/admin/client', name: 'app_client', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['nom', 'prenom', 'email', 'telephone'];
     
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if ($this->clientRepository->existsByEmail($data['email'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Cet email est déjà utilisé'
            ], JsonResponse::HTTP_CONFLICT);
        }
        $client = new Client();
        $client->setNom($data['nom']);
        $client->setPrenom($data['prenom']);
        $client->setEmail($data['email']);
        $client->setTelephone($data['telephone']);
        
        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Client créé avec succès',
            'client' => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'email' => $client->getEmail(),
                'telephone' => $client->getTelephone()
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/admin/client/{id}', name: 'app_get_client', methods: ['GET'])]
    public function getClient(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Client non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse([
            'id' => $client->getId(),
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'email' => $client->getEmail(),
            'telephone' => $client->getTelephone()
        ], JsonResponse::HTTP_OK);
    }   

    
    #[Route('/admin/clients', name: 'app_get_clients', methods: ['GET'])]
    public function getClients(): JsonResponse
    {
        $clients = $this->clientRepository->findAll();
        $clientData = [];
        foreach ($clients as $client) {
            $clientData[] = [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'email' => $client->getEmail(),
                'telephone' => $client->getTelephone()
            ];
        }
        return new JsonResponse($clientData, JsonResponse::HTTP_OK);
    }



    //put patch 
    #[Route('/admin/client/{id}', name: 'app_update_client', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Client non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);

        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['nom'])) {
            $client->setNom($data['nom']);
        }
        if (!empty($data['prenom'])) {
            $client->setPrenom($data['prenom']);
        }
        if (!empty($data['email'])) {
            if ($this->clientRepository->existsByEmail($data['email'], $id)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Cet email est déjà utilisé'
                ], JsonResponse::HTTP_CONFLICT);
            }
            $client->setEmail($data['email']);
        }
        if (!empty($data['telephone'])) {
            $client->setTelephone($data['telephone']);
            
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Client mis à jour avec succès',
            'client' => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'email' => $client->getEmail(),
                'telephone' => $client->getTelephone()
            ]
        ], JsonResponse::HTTP_OK);
    }
}
