<?php

namespace App\Controller;

use App\Entity\EquipeIntervention;
use App\Entity\Intervention;
use App\Entity\MaterielUtilise;
use App\Entity\Utilisateur;
use App\Repository\ClientRepository;
use App\Repository\DevisRepository;
use App\Repository\EquipeInterventionRepository;
use App\Repository\InterventionRepository;
use App\Repository\MaterielUtiliseRepository;
use App\Repository\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api')]
final class InterventionController extends AbstractController
{
      public function __construct(
        private EntityManagerInterface $entityManager,
        private DevisRepository $devisRepository,
        private InterventionRepository $interventionRepository,  
        private TerrainRepository $terrainRepository,
        private ClientRepository $clientRepository,
        private MaterielUtiliseRepository $materielUtiliseRepository,
        private EquipeInterventionRepository $equipeInterventionRepository,
    ) {}

    #[Route('/admin/interventions', name: 'app_interventions_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $interventions = $this->interventionRepository->findAll();
        

        $data = [];
        foreach ($interventions as $intervention) {
            // Build devis array from collection
            $devisArray = [];
            foreach ($intervention->getDevis() as $devis) {
                $devisArray[] = [
                    'id' => $devis->getId(),
                    'montant' => $devis->getMontantTotal(),
                ];
            }
            
            // Build terrain array from collection
            $terrainArray = [];
            foreach ($intervention->getTerrain() as $terrain) {
                $adresse = $terrain->getAdresse();
                $terrainArray[] = [
                    'id' => $terrain->getId(),
                    'superficie' => $terrain->getSuperficie(),
                    'adresse' => $adresse ? [
                        'nom' => $adresse['nom'] ?? null,
                        'rue' => $adresse['rue'] ?? null,
                        'codePostal' => $adresse['codePostal'] ?? null,
                        'ville' => $adresse['ville'] ?? null,
                    ] : null,
                ];
            }
            
            $data[] = [
                'id' => $intervention->getId(),
                'datePrevue' => $intervention->getDatePrevue()?->format('Y-m-d H:i:s'),
                'date_prevue' => $intervention->getDatePrevue()?->format('Y-m-d H:i:s'),
                'dateRealisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'date_realisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'commentaire' => $intervention->getCommentaire(),
                'materielUtiliseId' => $intervention->getMaterielUtilise()?->getId(),
                'materiel_utilise_id' => $intervention->getMaterielUtilise()?->getId(),
                'equipeInterventionId' => $intervention->getEquipeIntevention()?->getId(),
                'equipe_intervention_id' => $intervention->getEquipeIntevention()?->getId(),
                'devis' => $devisArray,
                'terrain' => $terrainArray,
                'planning' => [
                    'debut' => $intervention->getDatePrevue()?->format(\DateTimeInterface::ATOM),
                    'fin' => $intervention->getDateRealisation()?->format(\DateTimeInterface::ATOM),
                ],
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/planning/me', name: 'app_intervention_planning_me', methods: ['GET'])]
    public function myPlanning(Request $request): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Non authentifié'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $from = $this->parseDateTimeNullable($request->query->get('from'));
        if ($request->query->get('from') !== null && $from === null) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le paramètre 'from' est invalide"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $to = $this->parseDateTimeNullable($request->query->get('to'));
        if ($request->query->get('to') !== null && $to === null) {
            return new JsonResponse([
                'error' => true,
                'message' => "Le paramètre 'to' est invalide"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $interventions = $this->interventionRepository->findPlanningForUtilisateur(
            $user->getId(),
            $from,
            $to
        );

        $data = array_map(function (Intervention $intervention): array {
            $clientData = null;
            $terrainData = [];

            $terrains = $this->terrainRepository
                ->createQueryBuilder('t')
                ->leftJoin('t.client', 'tc')->addSelect('tc')
                ->leftJoin('t.typeTerrain', 'tt')->addSelect('tt')
                ->andWhere('IDENTITY(t.intervention) = :interventionId')
                ->setParameter('interventionId', $intervention->getId())
                ->getQuery()
                ->getResult();

            if ($terrains === []) {
                $terrains = $intervention->getTerrain()->toArray();
            }

            foreach ($terrains as $terrain) {
                $adresse = $terrain->getAdresse();
                $terrainNom = null;

                if (is_array($adresse)) {
                    $terrainNom = $adresse['nom'] ?? $adresse['adresse'] ?? null;
                }

                if ($terrainNom === null) {
                    $terrainNom = $terrain->getTypeTerrain()?->getNom();
                }

                if ($terrainNom === null) {
                    $terrainNom = sprintf('Terrain #%d', $terrain->getId());
                }

                $adresseData = null;
               
                if (is_array($adresse)) {
                    $adresseData = [
                        'nom' => $adresse['nom'] ?? null,
                        'rue' => $adresse['rue'] ?? null,
                        'codePostal' => $adresse['codePostal'] ?? null,
                        'ville' => $adresse['ville'] ?? null,
                    ];
                }

                $terrainData[] = [
                    'id' => $terrain->getId(),
                    'nom' => $terrainNom,
                    'superficie' => $terrain->getSuperficie(),
                    'adresse' => $adresseData,
                ];

                if ($clientData === null && $terrain->getClient() !== null) {
                    $clientData = [
                        'id' => $terrain->getClient()->getId(),
                        'nom' => $terrain->getClient()->getNom(),
                        'prenom' => $terrain->getClient()->getPrenom(),
                    ];
                }
            }

            if ($clientData === null) {
                $devisList = $this->devisRepository
                    ->createQueryBuilder('d')
                    ->leftJoin('d.client', 'dc')->addSelect('dc')
                    ->andWhere('IDENTITY(d.intervention) = :interventionId')
                    ->setParameter('interventionId', $intervention->getId())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult();

                if ($devisList === []) {
                    $devisList = $intervention->getDevis()->toArray();
                }

                foreach ($devisList as $devis) {
                    if ($devis->getClient() === null) {
                        continue;
                    }

                    $clientData = [
                        'id' => $devis->getClient()->getId(),
                        'nom' => $devis->getClient()->getNom(),
                        'prenom' => $devis->getClient()->getPrenom(),
                    ];
                    break;
                }
            }

            if ($clientData === null || $terrainData === []) {
                $meta = $this->extractPlanningMeta($intervention->getCommentaire() ?? '');

                if ($terrainData === [] && $meta['terrainId'] !== null) {
                    $terrainFromMeta = $this->terrainRepository->find($meta['terrainId']);
               
                    if ($terrainFromMeta !== null) {
                        $terrainNom = null;
                        $adresse = $terrainFromMeta->getAdresse();

                        if (is_array($adresse)) {
                            $terrainNom = $adresse['nom'] ?? $adresse['adresse'] ?? null;
                        }

                        if ($terrainNom === null) {
                            $terrainNom = $terrainFromMeta->getTypeTerrain()?->getNom();
                        }

                        if ($terrainNom === null) {
                            $terrainNom = sprintf('Terrain #%d', $terrainFromMeta->getId());
                        }
                 
                        $terrainData[] = [
                            'id' => $terrainFromMeta->getId(),
                            'nom' => $terrainNom,
                            'superficie' => $terrainFromMeta->getSuperficie(),
                            'adresse' => is_array($adresse) ? [
                                'rue' => $adresse['adresse'] ?? null,
                                'codePostal' => $adresse['cp'] ?? null,
                                'ville' => $adresse['nom'] ?? null,
                            ] : null,
                        ];

                        if ($clientData === null && $terrainFromMeta->getClient() !== null) {
                            $clientData = [
                                'id' => $terrainFromMeta->getClient()->getId(),
                                'nom' => $terrainFromMeta->getClient()->getNom(),
                                'prenom' => $terrainFromMeta->getClient()->getPrenom(),
                            ];
                        }
                    }
                }

                if ($clientData === null && $meta['clientId'] !== null) {
                    $clientFromMeta = $this->clientRepository->find($meta['clientId']);
                    if ($clientFromMeta !== null) {
                        $clientData = [
                            'id' => $clientFromMeta->getId(),
                            'nom' => $clientFromMeta->getNom(),
                            'prenom' => $clientFromMeta->getPrenom(),
                        ];
                    }
                }
            }

            $materielsData = [];
            $materielUtilise = $intervention->getMaterielUtilise();
            if ($materielUtilise !== null) {
                foreach ($materielUtilise->getMateriel() as $materiel) {
                    $materielsData[] = [
                        'id' => $materiel->getId(),
                        'libelle' => $materiel->getTypeMateriel()?->getLibelle(),
                    ];
                }
            }

            return [
                'id' => $intervention->getId(),
                'commentaire' => $intervention->getCommentaire(),
                'datePrevue' => $intervention->getDatePrevue()?->format('Y-m-d H:i:s'),
                'date_prevue' => $intervention->getDatePrevue()?->format('Y-m-d H:i:s'),
                'dateRealisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'date_realisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'planning' => [
                    'debut' => $intervention->getDatePrevue()?->format(\DateTimeInterface::ATOM),
                    'fin' => $intervention->getDateRealisation()?->format(\DateTimeInterface::ATOM),
                ],
                'equipeInterventionId' => $intervention->getEquipeIntevention()?->getId(),
                'equipe_intervention_id' => $intervention->getEquipeIntevention()?->getId(),
                'materielUtiliseId' => $intervention->getMaterielUtilise()?->getId(),
                'materiel_utilise_id' => $intervention->getMaterielUtilise()?->getId(),
                'client' => $clientData,
                'terrain' => $terrainData,
                'materiels' => $materielsData,
            ];
        }, $interventions);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /**
     * @return array{clientId: int|null, terrainId: int|null}
     */
    private function extractPlanningMeta(string $commentaire): array
    {
        $clientId = null;
        $terrainId = null;

        if (preg_match('/\[CLIENT:(\d+)\]/', $commentaire, $clientMatch) === 1) {
            $clientId = (int) $clientMatch[1];
        }

        if (preg_match('/\[TERRAIN:(\d+)\]/', $commentaire, $terrainMatch) === 1) {
            $terrainId = (int) $terrainMatch[1];
        }

        return [
            'clientId' => $clientId,
            'terrainId' => $terrainId,
        ];
    }
      
    //update
    #[Route('/admin/intervention/{id}', name: 'app_intervention_update', methods: ['PUT'])]
    public function update(int $id,Request $request): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Intervention non trouvée'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }

        $effectiveDatePrevue = $intervention->getDatePrevue();
        $effectiveDateRealisation = $intervention->getDateRealisation();
        $effectiveEquipe = $intervention->getEquipeIntevention();
        $effectiveMaterielUtilise = $intervention->getMaterielUtilise();

        if (!empty($data['datePrevue']) || !empty($data['date_prevue'])) {
            $datePrevue = $data['datePrevue'] ?? $data['date_prevue'];
            $parsedDatePrevue = $this->parsePlanningDateTime($datePrevue);
            if ($parsedDatePrevue === null) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La date prévue est invalide. Format attendu: Y-m-d H:i:s'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $effectiveDatePrevue = $parsedDatePrevue;
            $intervention->setDatePrevue($effectiveDatePrevue);
        }
        if (!empty($data['dateRealisation']) || !empty($data['date_realisation'])) {
            $dateRealisation = $data['dateRealisation'] ?? $data['date_realisation'];
            $parsedDateRealisation = $this->parsePlanningDateTime($dateRealisation);
            if ($parsedDateRealisation === null) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'La date de réalisation est invalide. Format attendu: Y-m-d H:i:s'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $effectiveDateRealisation = $parsedDateRealisation;
            $intervention->setDateRealisation($effectiveDateRealisation);
        }

        if ($effectiveDatePrevue === null || $effectiveDateRealisation === null) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Les champs datePrevue et dateRealisation sont requis pour le planning.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($effectiveDateRealisation <= $effectiveDatePrevue) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La date/heure de fin doit être strictement après la date/heure de début.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (isset($data['commentaire'])) {
            $intervention->setCommentaire($data['commentaire']);
        }
       if (isset($data['devisIds'])) {
            $intervention->getDevis()->clear();
            foreach ($data['devisIds'] as $devisId) {
                $devis = $this->devisRepository->find($devisId);
                if ($devis) {
                    $intervention->addDevi($devis);
                }
            }
        }
        if (isset($data['terrainIds'])) {
            $intervention->getTerrain()->clear();
            foreach ($data['terrainIds'] as $terrainId) {
                $terrain = $this->terrainRepository->find($terrainId);
                if ($terrain) {
                    $intervention->addTerrain($terrain);
                }
            }
        }

        if (isset($data['materielUtiliseId']) || array_key_exists('materiel_utilise_id', $data)) {
            $materielUtiliseId = $data['materielUtiliseId'] ?? $data['materiel_utilise_id'];
            if ($materielUtiliseId === null || $materielUtiliseId === '') {
                $intervention->setMaterielUtilise(null);
                $effectiveMaterielUtilise = null;
            } else {
                $materielUtilise = $this->materielUtiliseRepository->find((int) $materielUtiliseId);
                if (!$materielUtilise) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Matériel utilisé non trouvé'
                    ], JsonResponse::HTTP_NOT_FOUND);
                }
                $intervention->setMaterielUtilise($materielUtilise);
                $effectiveMaterielUtilise = $materielUtilise;
                $this->markMaterielAsUnavailable($materielUtilise);
            }
        }

        if (isset($data['equipeInterventionId']) || array_key_exists('equipe_intervention_id', $data)) {
            $equipeInterventionId = $data['equipeInterventionId'] ?? $data['equipe_intervention_id'];
            if ($equipeInterventionId === null || $equipeInterventionId === '') {
                $intervention->setEquipeIntevention(null);
                $effectiveEquipe = null;
            } else {
                $equipeIntervention = $this->equipeInterventionRepository->find((int) $equipeInterventionId);
                if (!$equipeIntervention) {
                    return new JsonResponse([
                        'error' => true,
                        'message' => 'Équipe intervention non trouvée'
                    ], JsonResponse::HTTP_NOT_FOUND);
                }
                $intervention->setEquipeIntevention($equipeIntervention);
                $effectiveEquipe = $equipeIntervention;
            }
        }

        $conflictResponse = $this->validateInterventionConflicts(
            $effectiveDatePrevue,
            $effectiveDateRealisation,
            $effectiveEquipe,
            $effectiveMaterielUtilise,
            $intervention->getId()
        );
        if ($conflictResponse !== null) {
            return $conflictResponse;
        }

        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Intervention mise à jour avec succès'
        ], JsonResponse::HTTP_OK);
    }
    //add
    #[Route('/admin/interventions', name: 'app_intervention_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }
        $requiredFields = ['commentaire'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis"
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $datePrevue = $data['datePrevue'] ?? $data['date_prevue'] ?? null;
        $dateRealisation = $data['dateRealisation'] ?? $data['date_realisation'] ?? null;
        if (empty($datePrevue) || empty($dateRealisation)) {
            return new JsonResponse([
                'error' => true,
                'message' => "Les champs 'datePrevue' et 'dateRealisation' sont requis"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $intervention = new Intervention();
        $datePrevueObject = $this->parsePlanningDateTime($datePrevue);
        if ($datePrevueObject === null) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La date prévue est invalide. Format attendu: Y-m-d H:i:s'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $intervention->setDatePrevue($datePrevueObject);
        $dateRealisationObject = $this->parsePlanningDateTime($dateRealisation);
        if ($dateRealisationObject === null) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La date de réalisation est invalide. Format attendu: Y-m-d H:i:s'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $intervention->setDateRealisation($dateRealisationObject);

        if ($dateRealisationObject <= $datePrevueObject) {
            return new JsonResponse([
                'error' => true,
                'message' => 'La date/heure de fin doit être strictement après la date/heure de début.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $intervention->setCommentaire($data['commentaire']);

        $effectiveMaterielUtilise = null;
        $effectiveEquipe = null;

        $materielUtiliseId = $data['materielUtiliseId'] ?? $data['materiel_utilise_id'] ?? null;
        if ($materielUtiliseId !== null && $materielUtiliseId !== '') {
            $materielUtilise = $this->materielUtiliseRepository->find((int) $materielUtiliseId);
            if (!$materielUtilise) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Matériel utilisé non trouvé'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $intervention->setMaterielUtilise($materielUtilise);
            $effectiveMaterielUtilise = $materielUtilise;
            $this->markMaterielAsUnavailable($materielUtilise);
        }

        $equipeInterventionId = $data['equipeInterventionId'] ?? $data['equipe_intervention_id'] ?? null;
        if ($equipeInterventionId !== null && $equipeInterventionId !== '') {
            $equipeIntervention = $this->equipeInterventionRepository->find((int) $equipeInterventionId);
            if (!$equipeIntervention) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Équipe intervention non trouvée'
                ], JsonResponse::HTTP_NOT_FOUND);
            }
            $intervention->setEquipeIntevention($equipeIntervention);
            $effectiveEquipe = $equipeIntervention;
        }

        $conflictResponse = $this->validateInterventionConflicts(
            $datePrevueObject,
            $dateRealisationObject,
            $effectiveEquipe,
            $effectiveMaterielUtilise
        );
        if ($conflictResponse !== null) {
            return $conflictResponse;
        }

        $this->entityManager->persist($intervention);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Intervention créée avec succès',
            'intervention' => [
                'id' => $intervention->getId(),
                'datePrevue' => $intervention->getDatePrevue()->format('Y-m-d H:i:s'),
                'date_prevue' => $intervention->getDatePrevue()->format('Y-m-d H:i:s'),
                'dateRealisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'date_realisation' => $intervention->getDateRealisation()?->format('Y-m-d H:i:s'),
                'commentaire' => $intervention->getCommentaire(),
                'materielUtiliseId' => $intervention->getMaterielUtilise()?->getId(),
                'materiel_utilise_id' => $intervention->getMaterielUtilise()?->getId(),
                'equipeInterventionId' => $intervention->getEquipeIntevention()?->getId(),
                'equipe_intervention_id' => $intervention->getEquipeIntevention()?->getId(),
                'planning' => [
                    'debut' => $intervention->getDatePrevue()?->format(\DateTimeInterface::ATOM),
                    'fin' => $intervention->getDateRealisation()?->format(\DateTimeInterface::ATOM),
                ],
            ]
        ], JsonResponse::HTTP_CREATED);
    }
    #[Route('/admin/intervention/{id}', name: 'app_intervention_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $intervention = $this->interventionRepository->find($id);
        if (!$intervention) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Intervention non trouvée'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($intervention);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Intervention supprimée avec succès'
        ], JsonResponse::HTTP_OK);
    }

    private function markMaterielAsUnavailable(MaterielUtilise $materielUtilise): void
    {
        foreach ($materielUtilise->getMateriel() as $materiel) {
            $materiel->setDisponible(false);
        }
    }

    private function validateInterventionConflicts(
        \DateTimeImmutable $datePrevue,
        \DateTimeImmutable $dateRealisation,
        ?EquipeIntervention $equipeIntervention,
        ?MaterielUtilise $materielUtilise,
        ?int $excludeInterventionId = null
    ): ?JsonResponse {
        if ($equipeIntervention !== null) {
            if ($this->interventionRepository->existsConflictForEquipeOnSlot(
                $equipeIntervention->getId(),
                $datePrevue,
                $dateRealisation,
                $excludeInterventionId
            )) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Cette équipe a déjà une intervention qui chevauche ce créneau.'
                ], JsonResponse::HTTP_CONFLICT);
            }

            $utilisateurIds = array_map(
                static fn ($utilisateur): int => $utilisateur->getId(),
                $equipeIntervention->getUtilisateurs()->toArray()
            );

            if ($this->interventionRepository->existsConflictForUtilisateursOnSlot(
                $utilisateurIds,
                $datePrevue,
                $dateRealisation,
                $excludeInterventionId
            )) {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Un ou plusieurs utilisateurs de cette équipe sont déjà affectés à une autre intervention qui chevauche ce créneau.'
                ], JsonResponse::HTTP_CONFLICT);
            }
        }

        if ($materielUtilise !== null && $this->interventionRepository->existsConflictForMaterielOnSlot(
            $materielUtilise->getId(),
            $datePrevue,
            $dateRealisation,
            $excludeInterventionId
        )) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Ce matériel est déjà utilisé sur une autre intervention qui chevauche ce créneau.'
            ], JsonResponse::HTTP_CONFLICT);
        }

        return null;
    }

    private function parseDateTimeNullable(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function parsePlanningDateTime(?string $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim($value);
        if ($normalizedValue === '') {
            return null;
        }

        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d\\TH:i:s',
            'Y-m-d H:i',
            'Y-m-d\\TH:i',
        ];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $normalizedValue);
            if ($date === false) {
                continue;
            }

            $errors = \DateTimeImmutable::getLastErrors();
            if (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
                continue;
            }

            return new \DateTimeImmutable($date->format('Y-m-d H:i:s'));
        }

        return null;
    }
}
