<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321104818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices CHANGE status status VARCHAR(20) NOT NULL COMMENT \'Текущий статус устройства (доступно, выдано, неисправно, в ремонте, списано)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices CHANGE status status VARCHAR(20) DEFAULT \'available\' NOT NULL COMMENT \'Текущий статус устройства (доступно, выдано, неисправно, в ремонте, списано)\'');
    }
}
