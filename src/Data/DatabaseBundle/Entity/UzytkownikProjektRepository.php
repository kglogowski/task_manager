<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Data\DatabaseBundle\Entity\Uzytkownik;
use Data\DatabaseBundle\Entity\Projekt;

/**
 * UzytkownikProjektRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UzytkownikProjektRepository extends EntityRepository {

    /**
     * 
     * @param Projekt $projekt
     * @param Uzytkownik $uzytkownik
     * @return UzytkownikProjekt
     */
    public function findByProjektAndUzytkownik($projekt, $uzytkownik) {
        $query = $this->getEntityManager()->createQuery("
            SELECT up
                FROM DataDatabaseBundle:UzytkownikProjekt up
                JOIN DataDatabaseBundle:Uzytkownik u WITH u.id = up.uzytkownik
                JOIN DataDatabaseBundle:Projekt p WITH p.id = up.projekt
                WHERE p.id = :projektId
                AND u.id = :uzytkownikId
        ");
        $query->setParameter(":projektId", $projekt->getId());
        $query->setParameter(":uzytkownikId", $uzytkownik->getId());
        return $query->getSingleResult();
    }

}
