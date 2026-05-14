<?php

namespace App\Controller;

use App\Entity\TypeTerrain;
use App\Repository\TypeTerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TypeTerrainController extends AbstractController
{
      public function __construct(
        private EntityManagerInterface $entityManager,
        private TypeTerrainRepository $typeTerrainRepository,
    ){}

    #[Route('/admin/type-terrain', name: 'app_type_terrains_index', methods: ['GET'])]
    public function typeTerrains(): JsonResponse
    {
        $typeTerrains = $this->typeTerrainRepository->findAll();
        $data = [];

        foreach ($typeTerrains as $typeTerrain) {
            $data[] = [
                'id' => $typeTerrain->getId(),
                'nom' => $typeTerrain->getNom(),    
                'description' => $typeTerrain->getDescription(),   
                
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
    #[Route('/admin/type-terrain/{id}', name: 'app_type_terrain', methods: ['GET'])]
    public function typeTerrainById(int $id): JsonResponse
    {
        $typeTerrain = $this->typeTerrainRepository->find($id);
        if (!$typeTerrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $typeTerrain->getId(),
            'nom' => $typeTerrain->getNom(),    
            'description' => $typeTerrain->getDescription(),   
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

        #[Route('/admin/type-terrain', name: 'app_type_terrains_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['nom', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        $typeTerrain = new TypeTerrain();
        $typeTerrain->setNom($data['nom']);
        $typeTerrain->setDescription($data['description']);
        $this->entityManager->persist($typeTerrain);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Type de terrain créé avec succès',
            'typeTerrain' => [
                'id' => $typeTerrain->getId(),
                'nom' => $typeTerrain->getNom(),
                'description' => $typeTerrain->getDescription(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

   #[Route('/admin/type-terrain/{id}', name: 'app_type_terrain_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $typeTerrain = $this->typeTerrainRepository->find($id);
        if (!$typeTerrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['nom'])) {
            $typeTerrain->setNom($data['nom']);
        }
        if (isset($data['description'])) {
            $typeTerrain->setDescription($data['description']);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de terrain mis à jour avec succès'
        ], JsonResponse::HTTP_OK);
    }
    #[Route('/admin/type-terrain/{id}', name: 'app_type_terrain_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $typeTerrain = $this->typeTerrainRepository->find($id);
        if (!$typeTerrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($typeTerrain);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de terrain supprimé avec succès'
        ]);
    }
}
