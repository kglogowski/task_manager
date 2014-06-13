<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use App\LibBundle\Base\BaseMigration;
use Data\DatabaseBundle\Entity\Grupa;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140613123430 extends BaseMigration
{
    public function up(Schema $schema)
    {
        $m = $this->getManager();
        $g1 = new Grupa();
        $g1->setNazwa("Analitycy");
        $g1->setKlasa("grupa_analitycy");
        $m->persist($g1);
        $m->flush();
    }

    public function down(Schema $schema)
    {

    }
}
