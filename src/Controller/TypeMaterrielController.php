<?php

namespace App\Controller;

use App\Entity\TypeMateriel;
use App\Repository\TypeMaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TypeMaterrielController extends AbstractController
{
      
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TypeMaterielRepository $typeMaterielRepository,
    ){}

    #[Route('/admin/type-materriel/', name: 'app_type_materiels_index', methods: ['GET'])]
    public function typeMatteriels(): JsonResponse
    {
        
        $typeMateriels = $this->typeMaterielRepository->findAll();
        $data = [];

        foreach ($typeMateriels as $typeMateriel) {
            $data[] = [
                'id' => $typeMateriel->getId(),
                'libelle' => $typeMateriel->getLibelle(),
                'transportable' => $typeMateriel->isTransportable(),
            ];
        }

        return new JsonResponse($data,JsonResponse::HTTP_OK);
    }

    #[Route('/admin/type-materriel/{id}', name: 'app_type_materriel', methods: ['GET'])]
    public function typeMaterielById(int $id): JsonResponse
    {
        $typeMateriel = $this->typeMaterielRepository->find($id);
        if (!$typeMateriel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $typeMateriel->getId(),
            'libelle' => $typeMateriel->getLibelle(),
            'transportable' => $typeMateriel->isTransportable(),
        ];
        return new JsonResponse($data,JsonResponse::HTTP_OK);
    }

    #[Route('/admin/type-materriel/', name: 'app_type_materriels_create', methods: ['POST'])]

    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['libelle'])) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le champ 'libelle' est requis"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!array_key_exists('transportable', $data)) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le champ 'transportable' est requis"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $typeMateriel = new TypeMateriel();
        $typeMateriel->setLibelle($data['libelle']);
        $typeMateriel->setTransportable((bool) $data['transportable']);
        $this->entityManager->persist($typeMateriel);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de matériel créé avec succès',
            'typeMateriel' => [
                'id' => $typeMateriel->getId(),
                'libelle' => $typeMateriel->getLibelle(),
                'transportable' => $typeMateriel->isTransportable(),
            ]
        ], JsonResponse::HTTP_CREATED);
        
    }

    #[Route('/admin/type-materriel/{id}', name: 'app_type_materriel_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse

    {
        $typeMateriel = $this->typeMaterielRepository->find($id);
        if (!$typeMateriel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($typeMateriel);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de matériel supprimé avec succès'
        ]);
    }
    #[Route('/admin/type-materriel/{id}', name: 'app_type_materriel_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $typeMateriel = $this->typeMaterielRepository->find($id);
        if (!$typeMateriel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['libelle'])) {
            $typeMateriel->setLibelle($data['libelle']);
            
        }
        if (isset($data['transportable'])) {
            $typeMateriel->setTransportable((bool) $data['transportable']);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de matériel mis à jour avec succès'
        ], JsonResponse::HTTP_OK);

    }
}

