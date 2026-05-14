<?php

namespace App\Controller;

use App\Entity\HistoriqueTerrain;
use App\Entity\Terrain;
use App\Repository\ClientRepository;
use App\Repository\TerrainRepository;
use App\Repository\TypeTerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TerrainController extends AbstractController
{

   public function __construct(
        private EntityManagerInterface $entityManager,
        private TerrainRepository $terrainRepository,
        private ClientRepository $clientRepository,
       private TypeTerrainRepository $typeTerrainRepository
    ){}

    #[Route('/admin/terrains/', name: 'app_terrains_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $terrains = $this->terrainRepository->findAll();
        $data = [];
        foreach ($terrains as $terrain) {
            $data[] = $this->serializeTerrain($terrain);
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/admin/terrain/{id}', name: 'app_terrain_show', methods: ['GET'])]
    public function detail(int $id): JsonResponse
    {
        $terrain = $this->terrainRepository->find($id);
        if (!$terrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = $this->serializeTerrain($terrain);
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/admin/terrain/client/{id}', name: 'app_terrain_by_client', methods: ['GET'])]
    public function terrainByClient(int $id): JsonResponse
    {
       
        $client = $this->clientRepository->find($id);
        if (!$client) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Client non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $terrains = $this->terrainRepository->findBy(['client' => $client]);
        $data = [];
        foreach ($terrains as $terrain) {
            $data[] = $this->serializeTerrain($terrain);
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
       
    }

    #[Route('/admin/terrain/', name: 'app_create_terrain', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['client_id', 'superficie', 'commentaire', 'type_terrain_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        if ($this->clientRepository->find($data['client_id']) === null) {
            return new JsonResponse([
                'error' => true,
                'message' => "Client avec l'id '{$data['client_id']}' non trouvé."
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $typeTerrain = $this->typeTerrainRepository->find($data['type_terrain_id']);
        if ($typeTerrain === null) {
            return new JsonResponse([
                'error' => true,
                'message' => "TypeTerrain avec l'id '{$data['type_terrain_id']}' non trouvé."
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $terrain = new Terrain();
        $terrain->setClient($this->clientRepository->find($data['client_id']));
        $terrain->setSuperficie($data['superficie']);
        $terrain->setCommentaire($data['commentaire']);
        $terrain->setTypeTerrain($typeTerrain);

        if (isset($data['adresse'])) {
            if (!$this->isValidAdresse($data['adresse'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ 'adresse' doit etre un objet contenant au minimum 'nom', 'cp' et 'adresse'."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setAdresse($data['adresse']);
        }

        if (isset($data['coordonnees_gps'])) {
            if (!$this->isValidCoordonneesGps($data['coordonnees_gps'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ 'coordonnees_gps' doit contenir 'latitude' et 'longitude' numeriques."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setCoordonneesGps($data['coordonnees_gps']);
        }

        if (isset($data['historique_terrain'])) {
            $historiqueValidation = $this->replaceHistoriqueTerrain($terrain, $data['historique_terrain']);
            if ($historiqueValidation instanceof JsonResponse) {
                return $historiqueValidation;
            }
        }

        $this->entityManager->persist($terrain);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Terrain créé avec succès',
            'terrain' => $this->serializeTerrain($terrain)
        ], JsonResponse::HTTP_CREATED);     

    }

    #[Route('/admin/terrain/{id}', name: 'app_delete_terrain', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $terrain = $this->terrainRepository->find($id);
        if (!$terrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($terrain);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Terrain supprimé avec succès'
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/admin/terrain/{id}', name: 'app_update_terrain', methods: ['PUT'])] 
    public function update(int $id, Request $request): JsonResponse
    {
        $terrain = $this->terrainRepository->find($id);
        if (!$terrain) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Terrain non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        if (isset($data['client_id'])) {
            $client = $this->clientRepository->find($data['client_id']);
            if (!$client) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Client avec l'id '{$data['client_id']}' non trouvé."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setClient($client);
        }
        if (isset($data['superficie'])) {
            $terrain->setSuperficie($data['superficie']);
        }
        if (isset($data['commentaire'])) {
            $terrain->setCommentaire($data['commentaire']);
        }
        if (isset($data['type_terrain_id'])) {
            $typeTerrain = $this->typeTerrainRepository->find($data['type_terrain_id']);
            if (!$typeTerrain) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "TypeTerrain avec l'id '{$data['type_terrain_id']}' non trouvé."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setTypeTerrain($typeTerrain);
        }
        if (isset($data['adresse'])) {
            if (!$this->isValidAdresse($data['adresse'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ 'adresse' doit etre un objet contenant au minimum 'nom', 'cp' et 'adresse'."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setAdresse($data['adresse']);
        }
        if (isset($data['coordonnees_gps'])) {
            if (!$this->isValidCoordonneesGps($data['coordonnees_gps'])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ 'coordonnees_gps' doit contenir 'latitude' et 'longitude' numeriques."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $terrain->setCoordonneesGps($data['coordonnees_gps']);
        }

        if (array_key_exists('historique_terrain', $data)) {
            $historiqueValidation = $this->replaceHistoriqueTerrain($terrain, $data['historique_terrain']);
            if ($historiqueValidation instanceof JsonResponse) {
                return $historiqueValidation;
            }
        }

        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Terrain mis à jour avec succès',
            'terrain' => $this->serializeTerrain($terrain)
        ], JsonResponse::HTTP_OK);
    }

    private function serializeTerrain(Terrain $terrain): array
    {
        return [
            'id' => $terrain->getId(),
            'client' => $terrain->getClient()->getId(),
            'superficie' => $terrain->getSuperficie(),
            'commentaire' => $terrain->getCommentaire(),
            'typeTerrain' => [
                'id' => $terrain->getTypeTerrain()->getId(),
                'nom' => $terrain->getTypeTerrain()->getNom(),
                'description' => $terrain->getTypeTerrain()->getDescription(),
            ],
            'historiqueTerrain' => array_map(
                static fn (HistoriqueTerrain $historiqueTerrain): array => [
                    'id' => $historiqueTerrain->getId(),
                    'isramassage' => $historiqueTerrain->isRamassage(),
                    'dateramage' => $historiqueTerrain->getDateRamassage()?->format('Y-m-d'),
                    'istonte' => $historiqueTerrain->isTonte(),
                    'dateTonte' => $historiqueTerrain->getDateTonte()?->format('Y-m-d'),
                ],
                $terrain->getHistoriqueTerrain()->toArray()
            ),
            'intervention' => $terrain->getIntervention() ? $terrain->getIntervention()->getId() : null,
            'adresse' => $terrain->getAdresse(),
            'coordonnees_gps' => $terrain->getCoordonneesGps(),
        ];
    }

    private function replaceHistoriqueTerrain(Terrain $terrain, mixed $historiqueTerrainData): ?JsonResponse
    {
        if (!is_array($historiqueTerrainData)) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le champ 'historique_terrain' doit etre un tableau."
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        foreach ($terrain->getHistoriqueTerrain()->toArray() as $existingHistorique) {
            $terrain->removeHistoriqueTerrain($existingHistorique);
            $this->entityManager->remove($existingHistorique);
        }

        foreach ($historiqueTerrainData as $index => $historiqueData) {
            if (!is_array($historiqueData)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "L'entree historique_terrain[$index] doit etre un objet."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            if (!array_key_exists('isramassage', $historiqueData) || !array_key_exists('istonte', $historiqueData)) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Les champs 'isramassage' et 'istonte' sont requis pour historique_terrain[$index]."
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $historiqueTerrain = new HistoriqueTerrain();
            $historiqueTerrain->setRamassage((bool) $historiqueData['isramassage']);
            $historiqueTerrain->setTonte((bool) $historiqueData['istonte']);

            if (!empty($historiqueData['dateramage'])) {
                try {
                    $historiqueTerrain->setDateRamassage(new \DateTimeImmutable($historiqueData['dateramage']));
                } catch (\Exception) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => "La date de ramassage est invalide pour historique_terrain[$index]."
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            if (!empty($historiqueData['dateTonte'])) {
                try {
                    $historiqueTerrain->setDateTonte(new \DateTimeImmutable($historiqueData['dateTonte']));
                } catch (\Exception) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => "La date de tonte est invalide pour historique_terrain[$index]."
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            $terrain->addHistoriqueTerrain($historiqueTerrain);
        }

        return null;
    }

    private function isValidAdresse(mixed $adresse): bool
    {
        if (!is_array($adresse)) {
            return false;
        }

        $requiredKeys = ['nom', 'cp', 'adresse'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $adresse) || !is_string($adresse[$key]) || trim($adresse[$key]) === '') {
                return false;
            }
        }

        return true;
    }

    private function isValidCoordonneesGps(mixed $coordonneesGps): bool
    {
        if (!is_array($coordonneesGps)) {
            return false;
        }

        if (!array_key_exists('latitude', $coordonneesGps) || !array_key_exists('longitude', $coordonneesGps)) {
            return false;
        }

        return is_numeric($coordonneesGps['latitude']) && is_numeric($coordonneesGps['longitude']);
    }
}
