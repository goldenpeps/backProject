<?php

namespace App\Controller;

use App\Entity\HistoriqueTerrain;
use App\Repository\HistoriqueTerrainRepository;
use App\Repository\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class HistoriqueTerrainController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HistoriqueTerrainRepository $historiqueTerrainRepository,
        private TerrainRepository $terrainRepository
    ) {
    }

    #[Route('/admin/historique-terrains', name: 'app_historique_terrains_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $historiques = $this->historiqueTerrainRepository->findAll();

        return new JsonResponse(array_map(fn (HistoriqueTerrain $historique): array => $this->serializeHistoriqueTerrain($historique), $historiques), JsonResponse::HTTP_OK);
    }

    #[Route('/admin/historique-terrains/{id}', name: 'app_historique_terrain_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $historique = $this->historiqueTerrainRepository->find($id);
        if (!$historique) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Historique terrain non trouve'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeHistoriqueTerrain($historique), JsonResponse::HTTP_OK);
    }

    #[Route('/admin/historique-terrain', name: 'app_historique_terrain_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['terrain_id', 'isramassage', 'istonte'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $terrain = $this->terrainRepository->find($data['terrain_id']);
        if (!$terrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Terrain non trouve'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $historique = new HistoriqueTerrain();
        $historique->setTerrain($terrain);
        $historique->setRamassage((bool) $data['isramassage']);
        $historique->setTonte((bool) $data['istonte']);

        if (!empty($data['dateramage'])) {
            try {
                $historique->setDateRamassage(new \DateTimeImmutable($data['dateramage']));
            } catch (\Exception) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La date de ramassage est invalide'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if (!empty($data['dateTonte'])) {
            try {
                $historique->setDateTonte(new \DateTimeImmutable($data['dateTonte']));
            } catch (\Exception) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La date de tonte est invalide'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $this->entityManager->persist($historique);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Historique terrain cree avec succes',
            'historiqueTerrain' => $this->serializeHistoriqueTerrain($historique)
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/admin/historique-terrain/{id}', name: 'app_historique_terrain_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $historique = $this->historiqueTerrainRepository->find($id);
        if (!$historique) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Historique terrain non trouve'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['terrain_id'])) {
            $terrain = $this->terrainRepository->find($data['terrain_id']);
            if (!$terrain) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Terrain non trouve'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $historique->setTerrain($terrain);
        }

        if (array_key_exists('isramassage', $data)) {
            $historique->setRamassage((bool) $data['isramassage']);
        }

        if (array_key_exists('istonte', $data)) {
            $historique->setTonte((bool) $data['istonte']);
        }

        if (array_key_exists('dateramage', $data)) {
            if ($data['dateramage'] === null || $data['dateramage'] === '') {
                $historique->setDateRamassage(null);
            } else {
                try {
                    $historique->setDateRamassage(new \DateTimeImmutable($data['dateramage']));
                } catch (\Exception) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'La date de ramassage est invalide'
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }
        }

        if (array_key_exists('dateTonte', $data)) {
            if ($data['dateTonte'] === null || $data['dateTonte'] === '') {
                $historique->setDateTonte(null);
            } else {
                try {
                    $historique->setDateTonte(new \DateTimeImmutable($data['dateTonte']));
                } catch (\Exception) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'La date de tonte est invalide'
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Historique terrain mis a jour avec succes',
            'historiqueTerrain' => $this->serializeHistoriqueTerrain($historique)
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/admin/historique-terrain/{id}', name: 'app_historique_terrain_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $historique = $this->historiqueTerrainRepository->find($id);
        if (!$historique) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Historique terrain non trouve'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($historique);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Historique terrain supprime avec succes'
        ], JsonResponse::HTTP_OK);
    }

    private function serializeHistoriqueTerrain(HistoriqueTerrain $historique): array
    {
        return [
            'id' => $historique->getId(),
            'terrain_id' => $historique->getTerrain()?->getId(),
            'isramassage' => $historique->isRamassage(),
            'dateramage' => $historique->getDateRamassage()?->format('Y-m-d'),
            'istonte' => $historique->isTonte(),
            'dateTonte' => $historique->getDateTonte()?->format('Y-m-d'),
        ];
    }
}
