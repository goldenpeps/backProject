<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331122354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe_intervention_utilisateur (equipe_intervention_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_A3AD3D55160C39A9 (equipe_intervention_id), INDEX IDX_A3AD3D55FB88E14F (utilisateur_id), PRIMARY KEY (equipe_intervention_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE equipe_intervention_utilisateur ADD CONSTRAINT FK_A3AD3D55160C39A9 FOREIGN KEY (equipe_intervention_id) REFERENCES equipe_intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_intervention_utilisateur ADD CONSTRAINT FK_A3AD3D55FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY `FK_1D1C63B3160C39A9`');
        $this->addSql('DROP INDEX IDX_1D1C63B3160C39A9 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP equipe_intervention_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipe_intervention_utilisateur DROP FOREIGN KEY FK_A3AD3D55160C39A9');
        $this->addSql('ALTER TABLE equipe_intervention_utilisateur DROP FOREIGN KEY FK_A3AD3D55FB88E14F');
        $this->addSql('DROP TABLE equipe_intervention_utilisateur');
        $this->addSql('ALTER TABLE utilisateur ADD equipe_intervention_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT `FK_1D1C63B3160C39A9` FOREIGN KEY (equipe_intervention_id) REFERENCES equipe_intervention (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3160C39A9 ON utilisateur (equipe_intervention_id)');
    }
}
