<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702134307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, member_id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, interval_notification VARCHAR(255) NOT NULL, interval_inactivity VARCHAR(255) NOT NULL, last_activity_date_time DATETIME NOT NULL, last_notification DATETIME DEFAULT NULL, notification_before_mail INT NOT NULL, current_notification_before_mail INT NOT NULL, channel_id VARCHAR(255) NOT NULL, tracking TINYINT(1) NOT NULL, email_address VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B723AF337597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE student');
    }
}
