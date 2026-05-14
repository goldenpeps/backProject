<?php

namespace App\Controller;

use App\Entity\MaterielUtilise;
use App\Repository\MaterielRepository;
use App\Repository\MaterielUtiliseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class MaterielUtiliseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MaterielUtiliseRepository $materielUtiliseRepository,
        private MaterielRepository $materielRepository,
    ) {}

    #[Route('/admin/materiels-utilises', name: 'app_materiel_utilise_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $materielUtilises = $this->materielUtiliseRepository->findAll();
        $data = [];
        foreach ($materielUtilises as $materielUtilise) {
            $materiels = [];
            foreach ($materielUtilise->getMateriel() as $materiel) {
                $materiels[] = [
                    'id' => $materiel->getId(),
                    'disponible' => $materiel->isDisponible(),
                ];
            }
            $data[] = [
                'id' => $materielUtilise->getId(),
                'durree' => $materielUtilise->getDurrer()?->format('Y-m-d'),
                'materiels' => $materiels,
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/admin/materiels-utilises/{id}', name: 'app_materiel_utilise_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $materielUtilise = $this->materielUtiliseRepository->find($id);
        if (!$materielUtilise) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel utilisé non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $materiels = [];
        foreach ($materielUtilise->getMateriel() as $materiel) {
            $materiels[] = [
                'id' => $materiel->getId(),
                'disponible' => $materiel->isDisponible(),
            ];
        }
        return new JsonResponse([
            'id' => $materielUtilise->getId(),
            'durree' => $materielUtilise->getDurrer()?->format('Y-m-d'),
            'materiels' => $materiels,
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/admin/materiels-utilises', name: 'app_materiel_utilise_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['durree'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        $materielUtilise = new MaterielUtilise();
        $materielUtilise->setDurrer(new \DateTimeImmutable($data['durree']));
        
        if (!empty($data['materielIds'])) {
            foreach ($data['materielIds'] as $materielId) {
                $materiel = $this->materielRepository->find($materielId);
                if ($materiel) {
                    $materielUtilise->addMateriel($materiel);
                }
            }
        }
        
        $this->entityManager->persist($materielUtilise);
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel utilisé créé avec succès',
            'materielUtilise' => [
                'id' => $materielUtilise->getId(),
                'durree' => $materielUtilise->getDurrer()->format('Y-m-d'),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/admin/materiels-utilises/{id}', name: 'app_materiel_utilise_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $materielUtilise = $this->materielUtiliseRepository->find($id);
        if (!$materielUtilise) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel utilisé non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['durree'])) {
            $materielUtilise->setDurrer(new \DateTimeImmutable($data['durree']));
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel utilisé mis à jour avec succès'
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/admin/materiels-utilises/{id}', name: 'app_materiel_utilise_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $materielUtilise = $this->materielUtiliseRepository->find($id);
        if (!$materielUtilise) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Matériel utilisé non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($materielUtilise);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Matériel utilisé supprimé avec succès'
        ], JsonResponse::HTTP_OK);
    }
}
