<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140611221257 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->exec("
            INSERT INTO roles (nazwa) VALUES ('ROLE_ADMIN');
        ");
        $this->connection->exec("
            INSERT INTO roles (nazwa) VALUES ('ROLE_USER');
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
