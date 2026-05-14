<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328164325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE devis (id INT AUTO_INCREMENT NOT NULL, date_devis DATE NOT NULL, montant_total DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, client_id INT NOT NULL, intervention_id INT DEFAULT NULL, INDEX IDX_8B27C52B19EB6921 (client_id), INDEX IDX_8B27C52B8EAE3863 (intervention_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE equipe_intervention (id INT AUTO_INCREMENT NOT NULL, commentaire LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE historique_terrain (id INT AUTO_INCREMENT NOT NULL, ramassage TINYINT NOT NULL, tonte TINYINT NOT NULL, date_ramassage DATE DEFAULT NULL, date_tonte DATE DEFAULT NULL, terrain_id INT NOT NULL, INDEX IDX_AA620CCE8A2D8B41 (terrain_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, date_prevue DATE NOT NULL, date_realisation DATE NOT NULL, commentaire LONGTEXT NOT NULL, materiel_utilise_id INT DEFAULT NULL, equipe_intevention_id INT DEFAULT NULL, INDEX IDX_D11814ABB9EDE018 (materiel_utilise_id), INDEX IDX_D11814AB374E7C6A (equipe_intevention_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ligne_devis (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_ligne DOUBLE PRECISION NOT NULL, devis_id INT NOT NULL, type_prestation_id INT NOT NULL, INDEX IDX_888B2F1B41DEFADA (devis_id), INDEX IDX_888B2F1BEEA87261 (type_prestation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE materiel (id INT AUTO_INCREMENT NOT NULL, disponible TINYINT NOT NULL, type_materiel_id INT NOT NULL, materiel_utilise_id INT DEFAULT NULL, INDEX IDX_18D2B0915D91DD3E (type_materiel_id), INDEX IDX_18D2B091B9EDE018 (materiel_utilise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE materiel_utilise (id INT AUTO_INCREMENT NOT NULL, durrer DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE terrain (id INT AUTO_INCREMENT NOT NULL, superficie DOUBLE PRECISION NOT NULL, commentaire LONGTEXT NOT NULL, adresse JSON DEFAULT NULL, coordonnees_gps JSON DEFAULT NULL, client_id INT NOT NULL, type_terrain_id INT NOT NULL, intervention_id INT DEFAULT NULL, INDEX IDX_C87653B119EB6921 (client_id), INDEX IDX_C87653B1972E5D64 (type_terrain_id), INDEX IDX_C87653B18EAE3863 (intervention_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_materiel (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, transportable TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_prestation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_terrain (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, equipe_intervention_id INT DEFAULT NULL, INDEX IDX_1D1C63B3160C39A9 (equipe_intervention_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE historique_terrain ADD CONSTRAINT FK_AA620CCE8A2D8B41 FOREIGN KEY (terrain_id) REFERENCES terrain (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABB9EDE018 FOREIGN KEY (materiel_utilise_id) REFERENCES materiel_utilise (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB374E7C6A FOREIGN KEY (equipe_intevention_id) REFERENCES equipe_intervention (id)');
        $this->addSql('ALTER TABLE ligne_devis ADD CONSTRAINT FK_888B2F1B41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE ligne_devis ADD CONSTRAINT FK_888B2F1BEEA87261 FOREIGN KEY (type_prestation_id) REFERENCES type_prestation (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B0915D91DD3E FOREIGN KEY (type_materiel_id) REFERENCES type_materiel (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091B9EDE018 FOREIGN KEY (materiel_utilise_id) REFERENCES materiel_utilise (id)');
        $this->addSql('ALTER TABLE terrain ADD CONSTRAINT FK_C87653B119EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE terrain ADD CONSTRAINT FK_C87653B1972E5D64 FOREIGN KEY (type_terrain_id) REFERENCES type_terrain (id)');
        $this->addSql('ALTER TABLE terrain ADD CONSTRAINT FK_C87653B18EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3160C39A9 FOREIGN KEY (equipe_intervention_id) REFERENCES equipe_intervention (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B19EB6921');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B8EAE3863');
        $this->addSql('ALTER TABLE historique_terrain DROP FOREIGN KEY FK_AA620CCE8A2D8B41');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814ABB9EDE018');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB374E7C6A');
        $this->addSql('ALTER TABLE ligne_devis DROP FOREIGN KEY FK_888B2F1B41DEFADA');
        $this->addSql('ALTER TABLE ligne_devis DROP FOREIGN KEY FK_888B2F1BEEA87261');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B0915D91DD3E');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091B9EDE018');
        $this->addSql('ALTER TABLE terrain DROP FOREIGN KEY FK_C87653B119EB6921');
        $this->addSql('ALTER TABLE terrain DROP FOREIGN KEY FK_C87653B1972E5D64');
        $this->addSql('ALTER TABLE terrain DROP FOREIGN KEY FK_C87653B18EAE3863');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3160C39A9');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE equipe_intervention');
        $this->addSql('DROP TABLE historique_terrain');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE ligne_devis');
        $this->addSql('DROP TABLE materiel');
        $this->addSql('DROP TABLE materiel_utilise');
        $this->addSql('DROP TABLE terrain');
        $this->addSql('DROP TABLE type_materiel');
        $this->addSql('DROP TABLE type_prestation');
        $this->addSql('DROP TABLE type_terrain');
        $this->addSql('DROP TABLE utilisateur');
    }
}
