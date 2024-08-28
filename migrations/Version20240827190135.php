<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827190135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, mark DOUBLE PRECISION DEFAULT NULL, max_mark DOUBLE PRECISION DEFAULT NULL, coefficient INT DEFAULT NULL, preview_sent TINYINT(1) NOT NULL, notification_sent TINYINT(1) NOT NULL, student_id INT DEFAULT NULL, INDEX IDX_1323A575CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE student ADD last_teacher_notification DATETIME DEFAULT NULL, ADD unseen_message_date_time DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575CB944F1A');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('ALTER TABLE student DROP last_teacher_notification, DROP unseen_message_date_time');
    }
}
