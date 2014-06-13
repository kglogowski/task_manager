<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use App\LibBundle\Base\BaseMigration;
use Data\DatabaseBundle\Entity\Uprawnienie;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140613115130 extends BaseMigration
{
    public function up(Schema $schema)
    {
        $m = $this->getManager();
        $uprawnienie1 = new Uprawnienie();
        $uprawnienie1->setNazwa("Tworzenie projektu");
        $uprawnienie1->setNumber("0000000001");
        $uprawnienie1->setBlokUprawnien($m->getRepository("DataDatabaseBundle:BlokUprawnien")->findOneByIdentyfikator('00001'));
        $m->persist($uprawnienie1);
        $m->flush();
    }

    public function down(Schema $schema)
    {

    }
}
