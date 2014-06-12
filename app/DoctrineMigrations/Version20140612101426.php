<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use App\LibBundle\Base\BaseMigration;
use Data\DatabaseBundle\Entity\Role;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140612101426 extends BaseMigration
{
    public function up(Schema $schema)
    {
        parent::up($schema);
        $role = new Role();
        $role->setNazwa('ROLE_ADMIN');
        $role2 = new Role();
        $role2->setNazwa('ROLE_USER');
        $m = $this->getManager();
        $m->persist($role);
        $m->persist($role2);
        $m->flush();
        
    }

    public function down(Schema $schema)
    {
        parent::down($schema);
        // this down() migration is auto-generated, please modify it to your needs

    }
}
