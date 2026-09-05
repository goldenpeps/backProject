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
     * Transforme un libelle accentue en identifiant simple utilisable dans un email.
     */
    private function slugify(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $ascii = false === $ascii ? $value : $ascii;

        return strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $ascii));
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
            ['julien.girard@jardin.fr', 'Girard', 'Julien', '0655667788', ['ROLE_USER']],
            ['manon.faure@jardin.fr', 'Faure', 'Manon', '0666778899', ['ROLE_USER']],
            ['hugo.perrin@jardin.fr', 'Perrin', 'Hugo', '0677889900', ['ROLE_USER']],
            ['chloe.lopez@jardin.fr', 'Lopez', 'Chloé', '0688990011', ['ROLE_USER']],
            ['maxime.rey@jardin.fr', 'Rey', 'Maxime', '0699001122', ['ROLE_USER']],
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
        $prenoms = [
            'Jean', 'Marie', 'Paul', 'Camille', 'Nicolas', 'Julie', 'Thomas', 'Claire', 'Antoine', 'Laura',
            'Sophie', 'Karim', 'Lucas', 'Emma', 'Julien', 'Manon', 'Hugo', 'Chloé', 'Maxime', 'Léa',
        ];
        $noms = [
            'Durand', 'Petit', 'Moreau', 'Girard', 'Bonnet', 'Roux', 'Fournier', 'Lambert', 'Simon', 'Michel',
            'Garcia', 'David', 'Bertrand', 'Morel', 'Fontaine', 'Chevalier', 'Robin', 'Masson', 'Sanchez', 'Nguyen',
        ];

        $nbClients = 60;
        $clients = [];
        for ($i = 0; $i < $nbClients; ++$i) {
            $prenom = $prenoms[$i % count($prenoms)];
            $nom = $noms[intdiv($i, count($prenoms)) % count($noms)];

            $client = new Client();
            $client->setNom($nom);
            $client->setPrenom($prenom);
            $client->setTelephone(sprintf('06%08d', 1020304 + $i * 11));
            $client->setEmail(sprintf('%s.%s@mail.fr', $this->slugify($prenom), $this->slugify($nom)));
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
            ['Talus engazonné', 'Forte pente nécessitant du matériel de tonte adapté'],
            ['Terrain avec arbres', 'Présence d\'arbres et de racines, attention lors de la tonte'],
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
            ['Taille-bordures', true],
            ['Scarificateur', false],
            ['Broyeur de végétaux', false],
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
            ['Scarification', 'Scarification de la pelouse', 40.0],
            ['Élagage', 'Élagage des arbres et arbustes', 55.0],
            ['Entretien massifs', 'Entretien des massifs floraux', 28.0],
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
            ['Équipe Est - intervention sur les communes de l\'est du secteur', [5, 6]],
            ['Équipe Ouest - intervention sur les communes de l\'ouest du secteur', [7, 8]],
            ['Équipe Centre - renfort ponctuel', [2, 9]],
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
        $nb = 60;
        $materielsUtilises = [];
        for ($i = 0; $i < $nb; ++$i) {
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
        $nb = 60;
        for ($i = 0; $i < $nb; ++$i) {
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
            'Passage scarification de printemps',
            'Élagage suite à demande client',
            'Entretien des massifs floraux',
        ];

        // Étalées sur environ 3 mois avant et 3 mois après aujourd'hui, pour que
        // le planning ait de la matière sur plusieurs semaines passées/futures.
        $nb = 180;
        $interventions = [];
        for ($i = 0; $i < $nb; ++$i) {
            $intervention = new Intervention();
            $heure = 8 + ($i % 8);
            $minute = ($i % 4) * 15;
            $datePrevue = (new \DateTimeImmutable(sprintf('%+d days', $i - 90)))->setTime($heure, $minute);
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
            ['rue' => '7 rue des Fleurs', 'codePostal' => '69003', 'ville' => 'Lyon'],
            ['rue' => '2 allée des Platanes', 'codePostal' => '69600', 'ville' => 'Oullins'],
            ['rue' => '9 rue du Stade', 'codePostal' => '69700', 'ville' => 'Givors'],
            ['rue' => '31 chemin des Vergers', 'codePostal' => '69110', 'ville' => 'Sainte-Foy-lès-Lyon'],
        ];

        $nb = 90;
        $nbInterventions = count($interventions);
        $terrains = [];
        for ($i = 0; $i < $nb; ++$i) {
            $client = $clients[$i % count($clients)];
            $ville = $villes[$i % count($villes)];

            $terrain = new Terrain();
            $terrain->setClient($client);
            $terrain->setSuperficie(round(150 + $i * 12.5, 1));
            $terrain->setCommentaire(sprintf('Terrain de %s %s, accès par le portail latéral', $client->getPrenom(), $client->getNom()));
            $terrain->setTypeTerrain($typesTerrain[$i % count($typesTerrain)]);
            $terrain->setAdresse([
                'nom' => sprintf('Terrain %s', $client->getNom()),
                'rue' => $ville['rue'],
                'codePostal' => $ville['codePostal'],
                'ville' => $ville['ville'],
            ]);
            $terrain->setCoordonneesGps([
                'lat' => round(45.75 + $i * 0.005, 6),
                'lng' => round(4.85 + $i * 0.005, 6),
            ]);

            // Chaque terrain pointe vers une intervention differente, etalee sur
            // toute la plage de dates disponible (et pas seulement les premieres),
            // pour que le planning affiche des vrais noms de client/terrain.
            if ($nbInterventions > 0) {
                $interventionIndex = intdiv($i * $nbInterventions, $nb) % $nbInterventions;
                $terrain->setIntervention($interventions[$interventionIndex]);
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

        $nb = 90;
        $devisList = [];
        for ($i = 0; $i < $nb; ++$i) {
            $devis = new Devis();
            $devis->setDateDevis(new \DateTimeImmutable(sprintf('-%d days', $i * 2)));
            $devis->setMontantTotal(round(80 + $i * 14.7, 2));
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
