<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20230702165158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('UPDATE comment SET created_at = CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE comment ALTER COLUMN created_at SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN comment.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comment DROP created_at');
    }
}
