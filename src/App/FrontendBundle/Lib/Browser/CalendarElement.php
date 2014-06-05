<?php

namespace App\FrontendBundle\Lib\Browser;

use App\FrontendBundle\Lib\Browser\Calendar;

class CalendarElement {

    private $day;
    private $dayOfWeek;
    private $month;
    private $year;
    private $uzytkownik;
    private $m;

    public function __construct($day, $dayOfWeek, $month, $year, $uzytkownik, $m) {
        $this->day = $day;
        $this->dayOfWeek = $dayOfWeek;
        $this->month = $month;
        $this->year = $year;
        $this->uzytkownik = $uzytkownik;
        $this->m = $m;
    }

    public function getEventsHtml($day) {
        $pr = $this->getProjektyToHtml($day);
        $t = $this->getTasksToHtml($day);
        return $pr.$t;
    }

    public function getProjektyToHtml($day) {
        $strReturn = '';
        $projekty = $this->getManager()->getRepository('DataDatabaseBundle:Projekt')->getProjektyByUzytkownikAndDate($this->getUzytkownik(), $day);
        foreach ($projekty as $projekt) {
            /* var $projekt \Data\DatabaseBundle\Entity\Projekt */
            $strReturn .= '<a href="'.$this->generateUrlProjekt($projekt).'"><div class="calendar_line" data-toggle="tooltip" data-placement="top" title="Przejdź do projektu">Zakończenie projektu "'.$projekt->getLabel().'"</div></a><br />';
        }
        return $strReturn;
    }
    public function getTasksToHtml($day) {
        $strReturn = '';
        $tasks = $this->getManager()->getRepository('DataDatabaseBundle:Task')->getTasksByUzytkownikAndDate($this->getUzytkownik(), $day);
        foreach ($tasks as $task) {
            /* var $task \Data\DatabaseBundle\Entity\Task */
            $strReturn .= '<a href="'.$this->generateUrlTask($task).'"><div class="calendar_line" data-toggle="tooltip" data-placement="top" title="Przejdź do zadania">Zakończenie zadania "'.$task->getLabel().'"</div></a><br />';
        }
        return $strReturn;
    }
    
    public function generateUrlProjekt(\Data\DatabaseBundle\Entity\Projekt $projekt) {
        return Calendar::GenerateRoute('tasks', array('projekt_nazwa' => $projekt->getName()));
    }
    
    public function generateUrlTask(\Data\DatabaseBundle\Entity\Task $task) {
        return Calendar::GenerateRoute('tasks', array(
            'projekt_nazwa' => $task->getProjekt()->getName(),
            'task_id'       => $task->getId()
        ));
    }

    public function generate() {
        $day = $this->generateDate();
        $dt = new \DateTime('now');
        $dayNow = $dt->format('Y-m-d');
        return '
            <div class="cloud_elem ' . ($day == $dayNow ? 'active_day' : '') . '">
                <div class="row well-sm">
                    <div class="col-md-1"><small>' . $day . '</small></div>
                    <div class="col-md-2">' . Calendar::GetLabelDayofWeek($this->getDayOfWeek()) . '</div>
                    <div class="col-md-6">' . $this->getEventsHtml($day) . '</div>
                </div>
            </div>
        ';
    }

    public function getDay() {
        return $this->day;
    }

    public function getDayOfWeek() {
        return $this->dayOfWeek;
    }

    public function getMonth() {
        return $this->month;
    }

    public function getYear() {
        return $this->year;
    }

    /**
     * 
     * @return \Data\DatabaseBundle\Entity\Uzytkownik
     */
    public function getUzytkownik() {
        return $this->uzytkownik;
    }

    public function getManager() {
        return $this->m;
    }

    public function generateDate() {
        return $this->year . '-' . $this->month . '-' . ($this->day >= 10 ? $this->day : '0' . $this->day);
    }

}
