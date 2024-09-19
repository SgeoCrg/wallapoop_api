<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240917085311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_hashtag ADD CONSTRAINT FK_8B6C31D14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_hashtag ADD CONSTRAINT FK_8B6C31D1FB34EF56 FOREIGN KEY (hashtag_id) REFERENCES hashtag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8B6C31D14584665A ON product_hashtag (product_id)');
        $this->addSql('CREATE INDEX IDX_8B6C31D1FB34EF56 ON product_hashtag (hashtag_id)');
        $this->addSql('ALTER TABLE user ADD avatar VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP avatar, DROP updated_at');
        $this->addSql('ALTER TABLE product_hashtag DROP FOREIGN KEY FK_8B6C31D14584665A');
        $this->addSql('ALTER TABLE product_hashtag DROP FOREIGN KEY FK_8B6C31D1FB34EF56');
        $this->addSql('DROP INDEX IDX_8B6C31D14584665A ON product_hashtag');
        $this->addSql('DROP INDEX IDX_8B6C31D1FB34EF56 ON product_hashtag');
    }
}
