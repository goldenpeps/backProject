<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Repository\MaterielRepository;
use App\Repository\TypeMaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class MaterielController extends AbstractController
{   
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MaterielRepository $materielRepository,
        private TypeMaterielRepository $typeMaterielRepository,
    ) {}
    
    #[Route('/admin/materiel', name: 'app_materiel_list', methods: ['GET'])]

    public function materiels(): JsonResponse
    {
        $materiels = $this->materielRepository->findAll();
        $data = [];
        foreach ($materiels as $materiel) {
            $data[] = [
                'id' => $materiel->getId(),
                'disponible' => $materiel->isDisponible(),
                'typeMateriel' => [
                    'id' => $materiel->getTypeMateriel()->getId(),
                    'libelle' => $materiel->getTypeMateriel()->getLibelle(),
                ],
            ];
        }

        return new JsonResponse($data, );
    }

    #[Route('/admin/materiel/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function materielById(int $id): JsonResponse
    {
        $materiel = $this->materielRepository->find($id);
        if (!$materiel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $materiel->getId(),
            'disponible' => $materiel->isDisponible(),
            'typeMateriel' => [
                'id' => $materiel->getTypeMateriel()->getId(),
                'libelle' => $materiel->getTypeMateriel()->getLibelle(),
            ],
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK); 
    }
    
    #[Route('/admin/materiel', name: 'app_materiel_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['disponible', 'typeMaterielId'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $typeMateriel = $this->typeMaterielRepository->find($data['typeMaterielId']);
        if (!$typeMateriel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $materiel = new Materiel();
        $materiel->setDisponible($data['disponible']);
        $materiel->setTypeMateriel($typeMateriel);

        $this->entityManager->persist($materiel);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel créé avec succès',
            'materiel' => [
                'id' => $materiel->getId(),
                'disponible' => $materiel->isDisponible(),
                'typeMateriel' => [
                    'id' => $typeMateriel->getId(),
                    'libelle' => $typeMateriel->getLibelle(),
                ],
            ]
        ], JsonResponse::HTTP_CREATED);

    }
    #[Route('/admin/materiel/{id}', name: 'app_delete_materiel', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $materiel = $this->materielRepository->find($id);
        if (!$materiel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }   

        $this->entityManager->remove($materiel);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel supprimé avec succès'
        ], JsonResponse::HTTP_OK);


    }

    #[Route('/admin/materiel/{id}', name: 'app_update_materiel', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $materiel = $this->materielRepository->find($id);
        if (!$materiel) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['disponible'])) {
            $materiel->setDisponible($data['disponible']);
        }
        if (!empty($data['typeMaterielId'])) {
            $typeMateriel = $this->typeMaterielRepository->find($data['typeMaterielId']);
            if (!$typeMateriel) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Type de matériel non trouvé'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $materiel->setTypeMateriel($typeMateriel);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel mis à jour avec succès',
            'materiel' => [
                'id' => $materiel->getId(),
                'disponible' => $materiel->isDisponible(),
                'typeMateriel' => [
                    'id' => $materiel->getTypeMateriel()->getId(),
                    'libelle' => $materiel->getTypeMateriel()->getLibelle(),
                ],
            ]
        ], JsonResponse::HTTP_OK);
    }

}
