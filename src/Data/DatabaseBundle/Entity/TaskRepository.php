<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Data\DatabaseBundle\Entity\Task;

/**
 * TaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskRepository extends EntityRepository {
    public function findByAktualnyUzytkownikNieZakonczone($user) {
        $query = $this->getEntityManager()->createQuery("
            SELECT t
                FROM DataDatabaseBundle:Task t
                WHERE t.aktualnyUzytkownik = :aktualnyUzytkownik
                AND t.status != :zamkniety
        ");
        $query->setParameter(":aktualnyUzytkownik", $user);
        $query->setParameter(":zamkniety", Task::STATUS_ZAMKNIETY);
        return $query->getResult();
    }
    
    public function findByPoprzedniUzytkownikNieZakonczone($user) {
        $query = $this->getEntityManager()->createQuery("
            SELECT t
                FROM DataDatabaseBundle:Task t
                WHERE t.poprzedniUzytkownik = :poprzedniUzytkownik
                AND t.status != :zamkniety
        ");
        $query->setParameter(":poprzedniUzytkownik", $user);
        $query->setParameter(":zamkniety", Task::STATUS_ZAMKNIETY);
        return $query->getResult();
    }
    
    public function getTasksByUzytkownikAndDate(Uzytkownik $uzytkownik, $date) {
        return $this->getEntityManager()->createQuery("
            SELECT t
                FROM DataDatabaseBundle:Task t
                LEFT JOIN DataDatabaseBundle:Projekt p WITH p.id = t.projekt
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND t.termin = :dt
        ")
                ->setParameter(':dt', $date)
                ->setParameter(':uzytkownik_id', $uzytkownik->getId())
                ->getResult();
    }

}
