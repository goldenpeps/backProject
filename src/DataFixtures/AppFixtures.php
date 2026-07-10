<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\EquipeIntervention;
use App\Entity\HistoriqueTerrain;
use App\Entity\Intervention;
use App\Entity\LigneDevis;
use App\Entity\Materiel;
use App\Entity\MaterielUtilise;
use App\Entity\Terrain;
use App\Entity\TypeMateriel;
use App\Entity\TypePrestation;
use App\Entity\TypeTerrain;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $utilisateurs = $this->loadUtilisateurs($manager);
        $clients = $this->loadClients($manager);
        $typesTerrain = $this->loadTypesTerrain($manager);
        $typesMateriel = $this->loadTypesMateriel($manager);
        $typesPrestation = $this->loadTypesPrestation($manager);
        $equipes = $this->loadEquipesIntervention($manager, $utilisateurs);
        $materielsUtilises = $this->loadMaterielsUtilises($manager);
        $this->loadMateriels($manager, $typesMateriel, $materielsUtilises);
        $interventions = $this->loadInterventions($manager, $materielsUtilises, $equipes);
        $terrains = $this->loadTerrains($manager, $clients, $typesTerrain, $interventions);
        $this->loadHistoriquesTerrain($manager, $terrains);
        $devis = $this->loadDevis($manager, $clients, $interventions);
        $this->loadLignesDevis($manager, $devis, $typesPrestation);

        $manager->flush();
    }

    /**
     * @return Utilisateur[]
     */
    private function loadUtilisateurs(ObjectManager $manager): array
    {
        $data = [
            ['admin@jardin.fr', 'Admin', 'Système', '0600000000', ['ROLE_ADMIN']],
            ['sophie.martin@jardin.fr', 'Martin', 'Sophie', '0611223344', ['ROLE_USER']],
            ['karim.benali@jardin.fr', 'Benali', 'Karim', '0622334455', ['ROLE_USER']],
            ['lucas.dubois@jardin.fr', 'Dubois', 'Lucas', '0633445566', ['ROLE_USER']],
            ['emma.leroy@jardin.fr', 'Leroy', 'Emma', '0644556677', ['ROLE_USER']],
        ];

        $utilisateurs = [];
        foreach ($data as [$email, $nom, $prenom, $telephone, $roles]) {
            $utilisateur = new Utilisateur();
            $utilisateur->setEmail($email);
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setTelephone($telephone);
            $utilisateur->setRoles($roles);
            $utilisateur->setIsActive(true);
            $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, 'password123'));
            $manager->persist($utilisateur);
            $utilisateurs[] = $utilisateur;
        }

        return $utilisateurs;
    }

    /**
     * @return Client[]
     */
    private function loadClients(ObjectManager $manager): array
    {
        $data = [
            ['Durand', 'Jean', '0601020304', 'jean.durand@mail.fr'],
            ['Petit', 'Marie', '0602030405', 'marie.petit@mail.fr'],
            ['Moreau', 'Paul', '0603040506', 'paul.moreau@mail.fr'],
            ['Girard', 'Camille', '0604050607', 'camille.girard@mail.fr'],
            ['Bonnet', 'Nicolas', '0605060708', 'nicolas.bonnet@mail.fr'],
            ['Roux', 'Julie', '0606070809', 'julie.roux@mail.fr'],
            ['Fournier', 'Thomas', '0607080910', 'thomas.fournier@mail.fr'],
            ['Lambert', 'Claire', '0608091011', 'claire.lambert@mail.fr'],
            ['Simon', 'Antoine', '0609101112', 'antoine.simon@mail.fr'],
            ['Michel', 'Laura', '0610111213', 'laura.michel@mail.fr'],
        ];

        $clients = [];
        foreach ($data as [$nom, $prenom, $telephone, $email]) {
            $client = new Client();
            $client->setNom($nom);
            $client->setPrenom($prenom);
            $client->setTelephone($telephone);
            $client->setEmail($email);
            $manager->persist($client);
            $clients[] = $client;
        }

        return $clients;
    }

    /**
     * @return TypeTerrain[]
     */
    private function loadTypesTerrain(ObjectManager $manager): array
    {
        $data = [
            ['Pelouse standard', 'Pelouse plane sans obstacle particulier'],
            ['Jardin fleuri', 'Jardin avec massifs de fleurs et bordures à contourner'],
            ['Terrain en pente', 'Terrain avec dénivelé nécessitant du matériel adapté'],
            ['Terrain synthétique', 'Gazon synthétique nécessitant un entretien spécifique'],
        ];

        $types = [];
        foreach ($data as [$nom, $description]) {
            $type = new TypeTerrain();
            $type->setNom($nom);
            $type->setDescription($description);
            $manager->persist($type);
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @return TypeMateriel[]
     */
    private function loadTypesMateriel(ObjectManager $manager): array
    {
        $data = [
            ['Tondeuse à gazon', true],
            ['Débroussailleuse', true],
            ['Taille-haie', true],
            ['Souffleur de feuilles', true],
            ['Tracteur tondeuse', false],
        ];

        $types = [];
        foreach ($data as [$libelle, $transportable]) {
            $type = new TypeMateriel();
            $type->setLibelle($libelle);
            $type->setTransportable($transportable);
            $manager->persist($type);
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @return TypePrestation[]
     */
    private function loadTypesPrestation(ObjectManager $manager): array
    {
        $data = [
            ['Tonte', 'Tonte de la pelouse', 35.0],
            ['Ramassage', 'Ramassage et évacuation des déchets verts', 20.0],
            ['Débroussaillage', 'Débroussaillage de terrain', 45.0],
            ['Taille de haie', 'Taille et mise en forme des haies', 30.0],
            ['Désherbage', 'Désherbage manuel ou mécanique', 25.0],
        ];

        $types = [];
        foreach ($data as [$nom, $description, $prix]) {
            $type = new TypePrestation();
            $type->setNom($nom);
            $type->setDescription($description);
            $type->setPrixUnitaire($prix);
            $manager->persist($type);
            $types[] = $type;
        }

        return $types;
    }

    /**
     * @param Utilisateur[] $utilisateurs
     *
     * @return EquipeIntervention[]
     */
    private function loadEquipesIntervention(ObjectManager $manager, array $utilisateurs): array
    {
        $data = [
            ['Équipe Nord - intervention sur les communes du nord du secteur', [1, 2]],
            ['Équipe Sud - intervention sur les communes du sud du secteur', [3, 4]],
            ['Équipe volante - renfort ponctuel', [1, 4]],
        ];

        $equipes = [];
        foreach ($data as [$commentaire, $membreIndexes]) {
            $equipe = new EquipeIntervention();
            $equipe->setCommentaire($commentaire);
            foreach ($membreIndexes as $index) {
                $equipe->addUtilisateur($utilisateurs[$index]);
            }
            $manager->persist($equipe);
            $equipes[] = $equipe;
        }

        return $equipes;
    }

    /**
     * @return MaterielUtilise[]
     */
    private function loadMaterielsUtilises(ObjectManager $manager): array
    {
        $materielsUtilises = [];
        for ($i = 0; $i < 5; ++$i) {
            $materielUtilise = new MaterielUtilise();
            $materielUtilise->setDurrer(new \DateTimeImmutable(sprintf('-%d days', $i * 3)));
            $manager->persist($materielUtilise);
            $materielsUtilises[] = $materielUtilise;
        }

        return $materielsUtilises;
    }

    /**
     * @param TypeMateriel[]     $typesMateriel
     * @param MaterielUtilise[]  $materielsUtilises
     */
    private function loadMateriels(ObjectManager $manager, array $typesMateriel, array $materielsUtilises): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $materiel = new Materiel();
            $materiel->setDisponible(0 !== $i % 3);
            $materiel->setTypeMateriel($typesMateriel[$i % count($typesMateriel)]);
            if (0 === $i % 2) {
                $materiel->setMaterielUtilise($materielsUtilises[$i % count($materielsUtilises)]);
            }
            $manager->persist($materiel);
        }
    }

    /**
     * @param MaterielUtilise[]     $materielsUtilises
     * @param EquipeIntervention[]  $equipes
     *
     * @return Intervention[]
     */
    private function loadInterventions(ObjectManager $manager, array $materielsUtilises, array $equipes): array
    {
        $commentaires = [
            'Tonte et ramassage standard',
            'Intervention urgente suite à demande client',
            'Entretien mensuel planifié',
            'Débroussaillage terrain difficile d\'accès',
            'Taille de haie et nettoyage',
        ];

        $interventions = [];
        for ($i = 0; $i < 15; ++$i) {
            $intervention = new Intervention();
            $datePrevue = new \DateTimeImmutable(sprintf('%+d days', $i * 2 - 10));
            $intervention->setDatePrevue($datePrevue);
            $intervention->setDateRealisation($datePrevue->modify('+2 hours'));
            $intervention->setCommentaire($commentaires[$i % count($commentaires)]);

            if (0 === $i % 2) {
                $intervention->setMaterielUtilise($materielsUtilises[$i % count($materielsUtilises)]);
            }
            if (0 === $i % 3) {
                $intervention->setEquipeIntevention($equipes[$i % count($equipes)]);
            }

            $manager->persist($intervention);
            $interventions[] = $intervention;
        }

        return $interventions;
    }

    /**
     * @param Client[]        $clients
     * @param TypeTerrain[]   $typesTerrain
     * @param Intervention[]  $interventions
     *
     * @return Terrain[]
     */
    private function loadTerrains(ObjectManager $manager, array $clients, array $typesTerrain, array $interventions): array
    {
        $villes = [
            ['rue' => '12 rue des Lilas', 'codePostal' => '69001', 'ville' => 'Lyon'],
            ['rue' => '5 avenue de la République', 'codePostal' => '69100', 'ville' => 'Villeurbanne'],
            ['rue' => '8 chemin des Vignes', 'codePostal' => '69300', 'ville' => 'Caluire-et-Cuire'],
            ['rue' => '20 rue Victor Hugo', 'codePostal' => '69200', 'ville' => 'Vénissieux'],
            ['rue' => '3 impasse des Tilleuls', 'codePostal' => '69500', 'ville' => 'Bron'],
            ['rue' => '15 route de Genas', 'codePostal' => '69800', 'ville' => 'Saint-Priest'],
        ];

        $terrains = [];
        for ($i = 0; $i < 12; ++$i) {
            $client = $clients[$i % count($clients)];
            $ville = $villes[$i % count($villes)];

            $terrain = new Terrain();
            $terrain->setClient($client);
            $terrain->setSuperficie(round(150 + $i * 37.5, 1));
            $terrain->setCommentaire(sprintf('Terrain de %s %s, accès par le portail latéral', $client->getPrenom(), $client->getNom()));
            $terrain->setTypeTerrain($typesTerrain[$i % count($typesTerrain)]);
            $terrain->setAdresse([
                'nom' => sprintf('Terrain %s', $client->getNom()),
                'rue' => $ville['rue'],
                'codePostal' => $ville['codePostal'],
                'ville' => $ville['ville'],
            ]);
            $terrain->setCoordonneesGps([
                'lat' => round(45.75 + $i * 0.01, 6),
                'lng' => round(4.85 + $i * 0.01, 6),
            ]);

            if (0 === $i % 4) {
                $terrain->setIntervention($interventions[$i % count($interventions)]);
            }

            $manager->persist($terrain);
            $terrains[] = $terrain;
        }

        return $terrains;
    }

    /**
     * @param Terrain[] $terrains
     */
    private function loadHistoriquesTerrain(ObjectManager $manager, array $terrains): void
    {
        foreach ($terrains as $i => $terrain) {
            for ($j = 0; $j < 2; ++$j) {
                $historique = new HistoriqueTerrain();
                $tonte = 0 !== ($i + $j) % 3;
                $ramassage = 0 === ($i + $j) % 2;
                $historique->setTonte($tonte);
                $historique->setRamassage($ramassage);
                $historique->setDateTonte($tonte ? new \DateTimeImmutable(sprintf('-%d days', ($i + $j) * 5)) : null);
                $historique->setDateRamassage($ramassage ? new \DateTimeImmutable(sprintf('-%d days', ($i + $j) * 5)) : null);
                $historique->setTerrain($terrain);
                $manager->persist($historique);
            }
        }
    }

    /**
     * @param Client[]        $clients
     * @param Intervention[]  $interventions
     *
     * @return Devis[]
     */
    private function loadDevis(ObjectManager $manager, array $clients, array $interventions): array
    {
        $statuts = ['en_attente', 'accepte', 'refuse', 'annule'];

        $devisList = [];
        for ($i = 0; $i < 10; ++$i) {
            $devis = new Devis();
            $devis->setDateDevis(new \DateTimeImmutable(sprintf('-%d days', $i * 4)));
            $devis->setMontantTotal(round(80 + $i * 23.4, 2));
            $devis->setStatus($statuts[$i % count($statuts)]);
            $devis->setClient($clients[$i % count($clients)]);

            if (0 === $i % 3) {
                $devis->setIntervention($interventions[$i % count($interventions)]);
            }

            $manager->persist($devis);
            $devisList[] = $devis;
        }

        return $devisList;
    }

    /**
     * @param Devis[]           $devisList
     * @param TypePrestation[]  $typesPrestation
     */
    private function loadLignesDevis(ObjectManager $manager, array $devisList, array $typesPrestation): void
    {
        foreach ($devisList as $i => $devis) {
            $nbLignes = 1 + $i % 3;
            for ($j = 0; $j < $nbLignes; ++$j) {
                $typePrestation = $typesPrestation[($i + $j) % count($typesPrestation)];
                $quantite = 1 + $j;

                $ligne = new LigneDevis();
                $ligne->setQuantite($quantite);
                $ligne->setPrixLigne(round($typePrestation->getPrixUnitaire() * $quantite, 2));
                $ligne->setDevis($devis);
                $ligne->setTypePrestation($typePrestation);
                $manager->persist($ligne);
            }
        }
    }
}
