<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605015808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, email VARCHAR(320) NOT NULL, password_hash VARCHAR(255) NOT NULL, verified_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, data_policy_accepted_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE verification_codes (id VARCHAR(36) NOT NULL, email VARCHAR(320) NOT NULL, code VARCHAR(6) NOT NULL, attempts SMALLINT NOT NULL, max_attempts SMALLINT NOT NULL, expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, resend_after TIMESTAMP(0) WITH TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_verification_codes_expires_at ON verification_codes (expires_at)');
        $this->addSql('CREATE UNIQUE INDEX uq_verification_codes_email ON verification_codes (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE verification_codes');
    }
}
