<?php

use Data\DatabaseBundle\Entity\Uzytkownik;

class CalendarBrowser {
    
    private $year;
    private $month;
    private $m;
    private $uzytkownik;

    
    
    public function __construct($year, $month, $m, Uzytkownik $uzytkownik) {
        $this->year = $year;
        $this->month = $month;
        $this->uzytkownik = $uzytkownik;
        $this->m = $m;
    }
    
    
    public function getFirstDayOfCalendar() {
        
    }

    public function getEvantsOfDay($year, $month, $day) {
        
    }
    
}
