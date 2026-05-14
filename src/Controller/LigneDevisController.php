<?php

namespace App\Controller;

use App\Entity\LigneDevis;
use App\Repository\DevisRepository;
use App\Repository\TypePrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class LigneDevisController extends AbstractController
{
        public function __construct(
        private EntityManagerInterface $entityManager,
        private DevisRepository $devisRepository,
        private TypePrestationRepository $typePrestationRepository,
    ) {}

    #[Route('/admin/ligne-devis/{devisId}', name: 'app_ligne_devis_by_devis', methods: ['GET'])]
    public function ligneDevisByDevis(int $devisId): JsonResponse
    {
        $devis = $this->devisRepository->find($devisId);
        if (!$devis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Devis non trouvé'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $ligneDevisList = $devis->getLigneDevis();
        $data = [];
        foreach ($ligneDevisList as $ligneDevis) {
            $data[] = [
                'id' => $ligneDevis->getId(),
                'typePrestation' => [
                    'id' => $ligneDevis->getTypePrestation()->getId(),
                    'nom' => $ligneDevis->getTypePrestation()->getNom(),
                    'description' => $ligneDevis->getTypePrestation()->getDescription(),
                    'prixUnitaire' => $ligneDevis->getTypePrestation()->getPrix

                ],
                'quantite' => $ligneDevis->getQuantite(),
                'prixTotal' => $ligneDevis->getPrixTotal(),
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
    //add
    #[Route('/admin/ligne-devis', name: 'app_ligne_devis_create', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        //envoie un devis mais plusier prestation

        $data = json_decode($request->getContent(), true);
        $requiredFields = ['devisId', 'typePrestationId', 'quantite'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse([
                    'error' => true,
                    'message' => "Le champ '$field' est requis."
                ], JsonResponse::HTTP_BAD_REQUEST);}
        }
        $devis = $this->devisRepository->find($data['devisId']);
        if (!$devis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Devis non trouvé.'
            ], JsonResponse::HTTP_NOT_FOUND);}
        $typePrestation = $this->typePrestationRepository->find($data['typePrestationId']);
        if (!$typePrestation) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Type de prestation non trouvé.'
            ], JsonResponse::HTTP_NOT_FOUND);}
        $ligneDevis = new LigneDevis();
        $ligneDevis->setDevis($devis);
        $ligneDevis->setTypePrestation($typePrestation);
        $ligneDevis->setQuantite($data['quantite']);
        $prixTotal = $typePrestation->getPrixUnitaire() * $data['quantite'];
        $ligneDevis->setPrixLigne($prixTotal);
        $this->entityManager->persist($ligneDevis);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Ligne de devis créée avec succès.',
            'ligneDevis' => [
                'id' => $ligneDevis->getId(),
                'devisId' => $devis->getId(),
                'typePrestationId' => $typePrestation->getId(),
                'quantite' => $ligneDevis->getQuantite(),
                'prixTotal' => $ligneDevis->getPrixLigne(),
            ]
        ], JsonResponse::HTTP_CREATED);
        
    }

    #[Route('/admin/ligne-devis/{id}', name: 'app_ligne_devis_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ligneDevis = $this->entityManager->getRepository(LigneDevis::class)->find($id);
        if (!$ligneDevis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Ligne de devis non trouvée.'
            ], JsonResponse::HTTP_NOT_FOUND);

        }
        $this->entityManager->remove($ligneDevis);
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Ligne de devis supprimée avec succès.'
        ], JsonResponse::HTTP_OK);
    }
    
    #[Route('/admin/ligne-devis/{id}', name: 'app_ligne_devis_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ligneDevis = $this->entityManager->getRepository(LigneDevis::class)->find($id);
        if (!$ligneDevis) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Ligne de devis non trouvée.'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!empty($data['quantite'])) {
            $ligneDevis->setQuantite($data['quantite']);
            $prixTotal = $ligneDevis->getTypePrestation()->getPrixUnitaire() * $data['quantite'];
            $ligneDevis->setPrixLigne($prixTotal);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Ligne de devis mise à jour avec succès.'
        ], JsonResponse::HTTP_OK); 
        
    } 
}
