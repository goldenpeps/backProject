<?php

namespace App\Controller;

use App\Entity\TypePrestation;
use App\Repository\TypePrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TypePrestationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TypePrestationRepository $typePrestationRepository,
    ){}
    #[Route('/admin/type-prestations/', name: 'app_type_prestations_index', methods: ['GET'])]
    public function typePrestations(): JsonResponse
    {
      $typePrestations = $this->typePrestationRepository->findAll();
        $data = [];
        foreach ($typePrestations as $typePrestation) {
            $data[] = [
                'id' => $typePrestation->getId(),
                'nom' => $typePrestation->getNom(),
                'description' => $typePrestation->getDescription(),
                'prixUnitaire' => $typePrestation->getPrixUnitaire(),
            ];
        }
        return new JsonResponse($data,JsonResponse::HTTP_OK);
    }
    #[Route('/admin/type-prestations/{id}', name: 'app_type_prestation', methods: ['GET'])]
    public function typePrestation(int $id): JsonResponse
    {
        $typePrestation = $this->typePrestationRepository->find($id);
        if (!$typePrestation) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de prestation non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $typePrestation->getId(),
            'nom' => $typePrestation->getNom(),
            'description' => $typePrestation->getDescription(),
            'prixUnitaire' => $typePrestation->getPrixUnitaire(),
        ];
        return new JsonResponse($data,JsonResponse::HTTP_OK);
    }
  

     #[Route('/admin/type-prestations/', name: 'app_type_prestations_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['nom', 'description', 'prixUnitaire'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        $typePrestation = new TypePrestation();
        $typePrestation->setNom($data['nom']);
        $typePrestation->setDescription($data['description']);
        $typePrestation->setPrixUnitaire($data['prixUnitaire']);
        $this->entityManager->persist($typePrestation);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Type de prestation créé avec succès',
            'typePrestation' => [
                'id' => $typePrestation->getId(),
                'nom' => $typePrestation->getNom(),
                'description' => $typePrestation->getDescription(),
                'prixUnitaire' => $typePrestation->getPrixUnitaire(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/admin/type-prestations/{id}', name: 'app_type_prestation_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $typePrestation = $this->typePrestationRepository->find($id);
        if (!$typePrestation) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de prestation non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);

        }
        $this->entityManager->remove($typePrestation);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de prestation supprimé avec succès'
        ]);
    }

    #[Route('/admin/type-prestations/{id}', name: 'app_type_prestation_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $typePrestation = $this->typePrestationRepository->find($id);
        if (!$typePrestation) {

            return new JsonResponse([
                'error' => true,
                'message' => 'Type de prestation non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['nom'])) {
            $typePrestation->setNom($data['nom']);
        }
        if (isset($data['description'])) {
            $typePrestation->setDescription($data['description']);
        }
        if (isset($data['prixUnitaire'])) {
            $typePrestation->setPrixUnitaire($data['prixUnitaire']);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Type de prestation mis à jour avec succès'
        ]);
    }
}
