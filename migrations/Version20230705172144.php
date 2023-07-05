<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20230705172144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD poster_id INT');
        $this->addSql('UPDATE comment SET poster_id = 1');
        $this->addSql('ALTER TABLE comment DROP name');
        $this->addSql('ALTER TABLE comment DROP email');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C5BB66C05 FOREIGN KEY (poster_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9474526C5BB66C05 ON comment (poster_id)');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8d5bb66c05');
        $this->addSql('DROP INDEX idx_5a8a6c8d5bb66c05');
        $this->addSql('ALTER TABLE post DROP poster_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post ADD poster_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8d5bb66c05 FOREIGN KEY (poster_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8d5bb66c05 ON post (poster_id)');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C5BB66C05');
        $this->addSql('DROP INDEX IDX_9474526C5BB66C05');
        $this->addSql('ALTER TABLE comment ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE comment ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE comment DROP poster_id');
    }
}
