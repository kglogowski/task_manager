<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use App\LibBundle\Base\BaseMigration;
use Data\DatabaseBundle\Entity\Grupa;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140613120533 extends BaseMigration
{
    public function up(Schema $schema)
    {
        $m = $this->getManager();
        $g1 = new Grupa();
        $g1->setNazwa("Programiści");
        $g1->setKlasa("grupa_programisci");
        $upr = $m->getRepository("DataDatabaseBundle:Uprawnienie")->findOneByNumber('0000000001');
        $upr->addGrupa($g1);
        $m->persist($upr);
        $m->flush();
    }

    public function down(Schema $schema)
    {

    }
}
