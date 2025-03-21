<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321103448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление комментариев к таблицам';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depots COMMENT = \'Депо (подразделения)\'');
        $this->addSql('ALTER TABLE devices COMMENT = \'Устройства (радиостанции, носители информации и др.)\'');
        $this->addSql('ALTER TABLE employees COMMENT = \'Сотрудники, получающие устройства\'');
        $this->addSql('ALTER TABLE logs COMMENT = \'Логи операций в системе\'');
        $this->addSql('ALTER TABLE transactions COMMENT = \'Операции выдачи/возврата устройств\'');
        $this->addSql('ALTER TABLE users COMMENT = \'Пользователи системы (операторы и администраторы)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depots COMMENT = \'\'');
        $this->addSql('ALTER TABLE devices COMMENT = \'\'');
        $this->addSql('ALTER TABLE employees COMMENT = \'\'');
        $this->addSql('ALTER TABLE logs COMMENT = \'\'');
        $this->addSql('ALTER TABLE transactions COMMENT = \'\'');
        $this->addSql('ALTER TABLE users COMMENT = \'\'');
    }
} 