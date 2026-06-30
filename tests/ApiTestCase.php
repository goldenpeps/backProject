<?php

namespace App\Tests;

use App\Entity\Utilisateur;
use App\Entity\TypeMateriel;
use App\Entity\TypePrestation;
use App\Entity\TypeTerrain;
use App\Entity\Materiel;
use App\Entity\Client;
use App\Entity\HistoriqueTerrain;
use App\Entity\Terrain;
use App\Entity\Devis;
use App\Entity\Intervention;
use App\Entity\MaterielUtilise;
use App\Entity\LigneDevis;
use App\Entity\EquipeIntervention;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected static function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createAuthenticatedClient(array $jwtPayload = []): object
    {
        $client = static::createClient();
        
        $email = $jwtPayload['email'] ?? 'test@example.com';
        
        // Create a test user if it doesn't exist
        $em = self::getEntityManager();
        $user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        $roles = $jwtPayload['roles'] ?? ['ROLE_USER'];
        if (!$user) {
            $user = new Utilisateur();
            $user->setEmail($email);
            $user->setPassword('hashed_password');
            $user->setNom('Test User');
            $user->setPrenom('Test');
            $user->setTelephone('0123456789');
            $user->setIsActive(true);
            $user->setRoles($roles);
            $em->persist($user);
            $em->flush();
        } else {
            $user->setRoles($roles);
            $em->flush();
        }
        
        $issuedAt = time();
        $expire = $issuedAt + 3600;

        $tokenPayload = array_merge($jwtPayload, [
            'iat' => $issuedAt,
            'exp' => $expire,
            'email' => $email,
        ]);

        // You need to set the JWT_SECRET from your .env.test
        $secret = $_ENV['JWT_SECRET'] ?? 'test_secret_key';
        $token = JWT::encode($tokenPayload, $secret, 'HS256');
        
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $token);
        
        return $client;
    }

    protected function createTestTypeMateriel(): TypeMateriel
    {
        $em = self::getEntityManager();
        $typeMateriel = new TypeMateriel();
        $typeMateriel->setLibelle('Test Materiel ' . uniqid());
        $typeMateriel->setTransportable(true);
        $em->persist($typeMateriel);
        $em->flush();
        return $typeMateriel;
    }

    protected function createTestTypePrestation(): TypePrestation
    {
        $em = self::getEntityManager();
        $typePrestation = new TypePrestation();
        $typePrestation->setNom('Test Prestation ' . uniqid());
        $typePrestation->setDescription('Test Description');
        $typePrestation->setPrixUnitaire(100.00);
        $em->persist($typePrestation);
        $em->flush();
        return $typePrestation;
    }

    protected function createTestTypeTerrain(): TypeTerrain
    {
        $em = self::getEntityManager();
        $typeTerrain = new TypeTerrain();
        $typeTerrain->setNom('Test Terrain ' . uniqid());
        $typeTerrain->setDescription('Test Description');
        $em->persist($typeTerrain);
        $em->flush();
        return $typeTerrain;
    }

    protected function createTestClient(): Client
    {
        $em = self::getEntityManager();
        $client = new Client();
        $client->setNom('Test Client');
        $client->setPrenom('Client');
        $client->setEmail('testclient' . uniqid() . '@example.com');
        $client->setTelephone('0123456789');
        $em->persist($client);
        $em->flush();
        return $client;
    }

    protected function createTestMateriel(): Materiel
    {
        $em = self::getEntityManager();
        $typeMateriel = $this->createTestTypeMateriel();
        $materiel = new Materiel();
        $materiel->setDisponible(true);
        $materiel->setTypeMateriel($typeMateriel);
        $em->persist($materiel);
        $em->flush();
        return $materiel;
    }

    protected function createTestHistoriqueTerrain(?Terrain $terrain = null): HistoriqueTerrain
    {
        $em = self::getEntityManager();
        $historiqueTerrain = new HistoriqueTerrain();
        $historiqueTerrain->setDateRamassage(new \DateTimeImmutable());
        $historiqueTerrain->setRamassage(true);
        $historiqueTerrain->setDateTonte(new \DateTimeImmutable());
        $historiqueTerrain->setTonte(true);
        $terrain = $terrain ?? $this->createTestTerrain(false);
        $historiqueTerrain->setTerrain($terrain);
        $terrain->addHistoriqueTerrain($historiqueTerrain);
        $em->persist($historiqueTerrain);
        $em->flush();
        return $historiqueTerrain;
    }

    protected function createTestTerrain(bool $withHistorique = true): Terrain
    {
        $em = self::getEntityManager();
        $client = $this->createTestClient();
        $typeTerrain = $this->createTestTypeTerrain();
        $terrain = new Terrain();
        $terrain->setClient($client);
        $terrain->setSuperficie(500.00);
        $terrain->setCommentaire('Test Terrain');
        $terrain->setTypeTerrain($typeTerrain);
        $em->persist($terrain);
        $em->flush();

        if ($withHistorique) {
            $this->createTestHistoriqueTerrain($terrain);
        }

        return $terrain;
    }

    protected function createTestDevis(): Devis
    {
        $em = self::getEntityManager();
        $client = $this->createTestClient();
        $devis = new Devis();
        $devis->setClient($client);
        $devis->setDateDevis(new \DateTimeImmutable());
        $devis->setMontantTotal(1000.00);
        $devis->setStatus('en_attente');
        $em->persist($devis);
        $em->flush();
        return $devis;
    }

    protected function createTestIntervention(): Intervention
    {
        $em = self::getEntityManager();
        $intervention = new Intervention();
        $intervention->setDatePrevue(new \DateTimeImmutable());
        $intervention->setDateRealisation(new \DateTimeImmutable());
        $intervention->setCommentaire('Test Intervention');
        $em->persist($intervention);
        $em->flush();
        return $intervention;
    }

    protected function createTestMaterielUtilise(): MaterielUtilise
    {
        $em = self::getEntityManager();
        $materielUtilise = new MaterielUtilise();
        $materielUtilise->setDurrer(new \DateTimeImmutable());
        $em->persist($materielUtilise);
        $em->flush();
        return $materielUtilise;
    }

    protected function createTestLigneDevis(): LigneDevis
    {
        $em = self::getEntityManager();
        $devis = $this->createTestDevis();
        $typePrestation = $this->createTestTypePrestation();
        $ligneDevis = new LigneDevis();
        $ligneDevis->setDevis($devis);
        $ligneDevis->setTypePrestation($typePrestation);
        $ligneDevis->setQuantite(2);
        $ligneDevis->setPrixLigne($typePrestation->getPrixUnitaire() * 2);
        $em->persist($ligneDevis);
        $em->flush();
        return $ligneDevis;
    }

    protected function createTestEquipeIntervention(): EquipeIntervention
    {
        $em = self::getEntityManager();
        $equipeIntervention = new EquipeIntervention();
        $equipeIntervention->setCommentaire('Test Equipe Intervention');
        $em->persist($equipeIntervention);
        $em->flush();
        return $equipeIntervention;
    }

    protected function createTestUtilisateur(): Utilisateur
    {
        $em = self::getEntityManager();
        $user = new Utilisateur();
        $user->setEmail('user' . uniqid() . '@example.com');
        $user->setPassword('hashed_password');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setTelephone('0123456789');
        $user->setIsActive(true);
        $em->persist($user);
        $em->flush();
        return $user;
    }
}
