<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use App\LibBundle\Base\BaseMigration;
use Data\DatabaseBundle\Entity\BlokUprawnien;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140613113058 extends BaseMigration
{
    public function up(Schema $schema)
    {
        $m = $this->getManager();
        $blokUprawnien1 = new BlokUprawnien();
        $blokUprawnien1->setIdentyfikator('00001');
        $blokUprawnien1->setNazwa("Frontend - projekt");
        $blokUprawnien2 = new BlokUprawnien();
        $blokUprawnien2->setIdentyfikator('00002');
        $blokUprawnien2->setNazwa("Frontend - task");
        $m->persist($blokUprawnien1);
        $m->persist($blokUprawnien2);
        $m->flush();
    }

    public function down(Schema $schema)
    {

    }
}
