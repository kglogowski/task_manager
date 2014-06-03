<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Data\DatabaseBundle\Entity\Uzytkownik;
use Data\DatabaseBundle\Entity\UzytkownikProjekt;

/**
 * ProjektRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjektRepository extends EntityRepository {
    
    public function findUzytkownicyByProjekt(Projekt $projekt) {
        return $this->createQueryBuilder('p')
                ->select('u')
                ->join('DataDatabaseBundle:UzytkownikProjekt','up', 'WITH', 'up.projekt = p.id')
                ->join('DataDatabaseBundle:Uzytkownik','u', 'WITH', 'up.uzytkownik = u.id')
                ->where('p.id = ' . $projekt->getId())
                ->orderBy('u.nazwisko')
                ->getQuery()
                ->getResult()
        ;
    }
    
    public function deleteProjekt(Projekt $projekt) {
       $this->getEntityManager()
                ->createQueryBuilder()
                ->delete('DataDatabaseBundle:Projekt', 'u')
                ->where('u.id = '.$projekt->getId())
                ->getQuery()
                ->execute();
    }
    
    public function addToDeleteProjekt(Projekt $projekt){
        $this->getEntityManager()
                ->createQueryBuilder()
                ->update('DataDatabaseBundle:Projekt', 'u')
                ->set('u.skasowane', 'true')
                ->where('u.id = ' . $projekt->getId())
                ->getQuery()
                ->execute();
    }
    
        public function removeFromDeleteProjekt(Projekt $projekt){
        $this->getEntityManager()
                ->createQueryBuilder()
                ->update('DataDatabaseBundle:Projekt', 'u')
                ->set('u.skasowane', 'null')
                ->where('u.id = ' . $projekt->getId())
                ->getQuery()
                ->execute();
    }
      
}
