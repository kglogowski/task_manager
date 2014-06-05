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
                        ->join('DataDatabaseBundle:UzytkownikProjekt', 'up', 'WITH', 'up.projekt = p.id')
                        ->join('DataDatabaseBundle:Uzytkownik', 'u', 'WITH', 'up.uzytkownik = u.id')
                        ->where('p.id = ' . $projekt->getId())
                        ->orderBy('u.nazwisko')
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function addToDeleteProjekt(Projekt $projekt) {
        $this->getEntityManager()
                ->createQueryBuilder()
                ->update('DataDatabaseBundle:Projekt', 'u')
                ->set('u.skasowane', 'true')
                ->where('u.id = ' . $projekt->getId())
                ->getQuery()
                ->execute();
    }

    public function removeFromDeleteProjekt(Projekt $projekt) {
        $this->getEntityManager()
                ->createQueryBuilder()
                ->update('DataDatabaseBundle:Projekt', 'u')
                ->set('u.skasowane', 'null')
                ->where('u.id = ' . $projekt->getId())
                ->getQuery()
                ->execute();
    }

    public function getProjectByUser(Uzytkownik $uzytkownik) {
        return $this->getEntityManager()->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.status != :status_zamkniety
                AND p.skasowane is null
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                        ->getResult();
    }

    public function getRestOfProject(Uzytkownik $uzytkownik) {
        $projektSubQuery = $this->getEntityManager()->createQuery("                    
            SELECT pp.id
                        FROM DataDatabaseBundle:UzytkownikProjekt up
                        JOIN DataDatabaseBundle:Projekt pp WITH pp.id = up.projekt
                        WHERE up.uzytkownik = :uzytkownik_id
        ")
                ->setParameter(':uzytkownik_id', $uzytkownik->getId())
                ->getResult();
        return $this->getEntityManager()->createQuery("
            SELECT p
                FROM DataDatabaseBundle:Projekt p
                WHERE p.id NOT IN (
                    :array
                )
                AND p.status != :status_zamkniety
        ")
                        ->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                        ->setParameter(':array', $projektSubQuery)
                        ->getResult();
    }

    public function getProjectByUserZakonczone(Uzytkownik $uzytkownik) {
        return $this->getEntityManager()->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.status = :status_zamkniety
                AND p.skasowane is null
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                        ->getResult();
    }

    public function getRestOfProjectZakonczone(Uzytkownik $uzytkownik) {
        $projektSubQuery = $this->getEntityManager()->createQuery("                    
            SELECT pp.id
                        FROM DataDatabaseBundle:UzytkownikProjekt up
                        JOIN DataDatabaseBundle:Projekt pp WITH pp.id = up.projekt
                        WHERE up.uzytkownik = :uzytkownik_id
        ")
                ->setParameter(':uzytkownik_id', $uzytkownik->getId())
                ->getResult();
        return $this->getEntityManager()->createQuery("
            SELECT p
                FROM DataDatabaseBundle:Projekt p
                WHERE p.id NOT IN (
                    :array
                )
                AND p.status = :status_zamkniety
        ")
                        ->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                        ->setParameter(':array', $projektSubQuery)
                        ->getResult();
    }

    public function getProjectByUserSkasowane(Uzytkownik $uzytkownik) {
        return $this->getEntityManager()->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.skasowane = true
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())
                        ->getResult();
    }

    public function getRestOfProjectSkasowane(Uzytkownik $uzytkownik) {
        $projektSubQuery = $this->getEntityManager()->createQuery("                    
            SELECT pp.id
                        FROM DataDatabaseBundle:UzytkownikProjekt up
                        JOIN DataDatabaseBundle:Projekt pp WITH pp.id = up.projekt
                        WHERE up.uzytkownik = :uzytkownik_id
        ")
                ->setParameter(':uzytkownik_id', $uzytkownik->getId())
                ->getResult();
        
        return $this->getEntityManager()->createQuery("
            SELECT p
                FROM DataDatabaseBundle:Projekt p
                WHERE p.id NOT IN (
                    :array
                )
                AND p.skasowane = true
        ")
                        ->setParameter(':array', $projektSubQuery)
                        ->getResult();
    }
    
    public function getProjektyByUzytkownikAndDate(Uzytkownik $uzytkownik, $date) {
        return $this->getEntityManager()->createQuery("
            SELECT p
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.termin = :dt
        ")
                ->setParameter(':dt', $date)
                ->setParameter(':uzytkownik_id', $uzytkownik->getId())
                ->getResult();
    }

}
