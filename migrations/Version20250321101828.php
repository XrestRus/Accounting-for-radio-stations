<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321101828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE depots (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devices (id INT AUTO_INCREMENT NOT NULL, depot_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, serial_number VARCHAR(50) NOT NULL, qr_code VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, write_off_comment LONGTEXT DEFAULT NULL, write_off_date DATETIME DEFAULT NULL, repair_comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_11074E9AD948EE2 (serial_number), INDEX IDX_11074E9A8510D4DE (depot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employees (id INT AUTO_INCREMENT NOT NULL, depot_id INT DEFAULT NULL, full_name VARCHAR(100) NOT NULL, position VARCHAR(100) NOT NULL, department VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_BA82C3008510D4DE (depot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, device_id INT DEFAULT NULL, action VARCHAR(100) NOT NULL, details LONGTEXT DEFAULT NULL, details_meta JSON DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_F08FC65CA76ED395 (user_id), INDEX IDX_F08FC65C94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, employee_id INT NOT NULL, issued_by_id INT NOT NULL, issued_at DATETIME NOT NULL, returned_at DATETIME DEFAULT NULL, due_date DATETIME NOT NULL, return_status VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_EAA81A4C94A4C7D4 (device_id), INDEX IDX_EAA81A4C8C03F15C (employee_id), INDEX IDX_EAA81A4C784BB717 (issued_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, depot_id INT DEFAULT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), INDEX IDX_1483A5E98510D4DE (depot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE devices ADD CONSTRAINT FK_11074E9A8510D4DE FOREIGN KEY (depot_id) REFERENCES depots (id)');
        $this->addSql('ALTER TABLE employees ADD CONSTRAINT FK_BA82C3008510D4DE FOREIGN KEY (depot_id) REFERENCES depots (id)');
        $this->addSql('ALTER TABLE logs ADD CONSTRAINT FK_F08FC65CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE logs ADD CONSTRAINT FK_F08FC65C94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C8C03F15C FOREIGN KEY (employee_id) REFERENCES employees (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C784BB717 FOREIGN KEY (issued_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E98510D4DE FOREIGN KEY (depot_id) REFERENCES depots (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices DROP FOREIGN KEY FK_11074E9A8510D4DE');
        $this->addSql('ALTER TABLE employees DROP FOREIGN KEY FK_BA82C3008510D4DE');
        $this->addSql('ALTER TABLE logs DROP FOREIGN KEY FK_F08FC65CA76ED395');
        $this->addSql('ALTER TABLE logs DROP FOREIGN KEY FK_F08FC65C94A4C7D4');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C94A4C7D4');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C8C03F15C');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C784BB717');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E98510D4DE');
        $this->addSql('DROP TABLE depots');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE employees');
        $this->addSql('DROP TABLE logs');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
