<?php

namespace App\FrontendBundle\Lib\Browser;

use Data\DatabaseBundle\Entity\Uzytkownik;
use App\FrontendBundle\Lib\Browser\CalendarElement;

class Calendar {

    private $year;
    private $month;
    private $m;
    private $uzytkownik;
    private $arrItems;

    public function __construct($year, $month, $m, Uzytkownik $uzytkownik) {
        $this->year = $year;
        $this->month = $month;
        $this->uzytkownik = $uzytkownik;
        $this->m = $m;
        $this->arrItems = array();
        $this->generateCalendar();
    }

    CONST STYCZEN = 1;
    CONST LUTY = 2;
    CONST MARZEC = 3;
    CONST KWIECIEN = 4;
    CONST MAJ = 5;
    CONST CZERWIEC = 6;
    CONST LIPIEC = 7;
    CONST SIERPIEN = 8;
    CONST WRZESIEN = 9;
    CONST PAZDZIERNIK = 10;
    CONST LISTOPAD = 11;
    CONST GRUDZIEN = 12;

    protected static $arrMonthLabel = array(
        self::STYCZEN => 'Styczeń',
        self::LUTY => 'Luty',
        self::MARZEC => 'Marzec',
        self::KWIECIEN => 'Kwiecień',
        self::MAJ => 'Maj',
        self::CZERWIEC => 'Czerwiec',
        self::LIPIEC => 'Lipiec',
        self::SIERPIEN => 'Sierpień',
        self::WRZESIEN => 'Wrzesień',
        self::PAZDZIERNIK => 'Październik',
        self::LISTOPAD => 'Listopad',
        self::GRUDZIEN => 'Grudzień',
    );

    public static function GetLabelMonth($month) {
        return self::$arrMonthLabel[$month];
    }

    CONST PONIEDZIALEK = 1;
    CONST WTOREK = 2;
    CONST SRODA = 3;
    CONST CZWARTEK = 4;
    CONST PIATEK = 5;
    CONST SOBOTA = 6;
    CONST NIEDZIELA = 7;

    protected static $arrDayOfWeekLabel = array(
        self::PONIEDZIALEK => 'Poniedziałek',
        self::WTOREK => 'Wtorek',
        self::SRODA => 'Środa',
        self::CZWARTEK => 'Czwartek',
        self::PIATEK => 'Piatek',
        self::SOBOTA => 'Sobota',
        self::NIEDZIELA => 'Niedziela',
    );

    public static function GetLabelDayofWeek($dayOfWeek) {
        return self::$arrDayOfWeekLabel[$dayOfWeek];
    }

    public function getArrMonthLabel() {
        return $this->arrMonthLabel;
    }

    public function getYear() {
        return $this->year;
    }

    public function getMonth() {
        return $this->month;
    }

    public function getM() {
        return $this->m;
    }

    public function getUzytkownik() {
        return $this->uzytkownik;
    }

    public function getLengthOfMonth() {
        return cal_days_in_month(CAL_GREGORIAN, $this->getMonth(), $this->getYear());
    }

    public function getFirstDayOfWeekFromCalendar() {
        $dt = new \DateTime($this->getYear() . '-' . $this->getMonth() . '-01');
        return date_format($dt, 'N');
    }

    public function getDayOfWeek($day) {
        $dt = new \DateTime($this->getYear() . '-' . $this->getMonth() . '-' . $day);
        return date_format($dt, 'N');
    }

    public function nextDayOfWeek($day) {
        if ($day == 7) {
            return 1;
        } else {
            return ++$day;
        }
    }

    private function generateCalendar() {
        $length = $this->getLengthOfMonth();
        for ($i = 1; $i <= $length; $i++) {
            $objCE = new CalendarElement($i, $this->getDayOfWeek($i), $this->getMonth(), $this->getYear(), $this->getUzytkownik(), $this->getM());
            $this->addItem($objCE);
        }
    }

    public function addItem($objItem) {
        $this->arrItems[] = $objItem;
    }

    /**
     * 
     * @return array CalendarElement
     */
    public function getItems() {
        return $this->arrItems;
    }

//    public function __toString() {
//        $return = '
//            <div class="cloud">
//                <div class="cloud_tt">
//                    <h3>Kalendarz na ' . Calendar::GetLabelMonth((int) $this->getMonth()) . ' ' . $this->getYear() . '</h3>
//                </div>
//                <div class="cloud_ct">
//                <div class="cloud_elem_th">
//                    <div class="row well-sm">
//                        <div class="col-md-1">Lp</div>
//                        <div class="col-md-2">Dzień tygodnia</div>
//                        <div class="col-md-6">Wydarzenia</div>
//                    </div>
//                </div>
//        ';
//        foreach ($this->getItems() as $objItem) {
//            /* @var $objItem CalendarElement */
//            $return.=$objItem->generate();
//        }
//        $return.='</div></div>';
//        echo $return;
//        return '';
//    }
    
    public function generate() {
        $return = '
            <div class="cloud">
                <div class="cloud_tt">
                    <h3>Kalendarz na ' . Calendar::GetLabelMonth((int) $this->getMonth()) . ' ' . $this->getYear() . '</h3>
                </div>
                <div class="cloud_ct">
                <div class="cloud_elem_th">
                    <div class="row well-sm">
                        <div class="col-md-1">Lp</div>
                        <div class="col-md-2">Dzień tygodnia</div>
                        <div class="col-md-6">Wydarzenia</div>
                    </div>
                </div>
        ';
        foreach ($this->getItems() as $objItem) {
            /* @var $objItem CalendarElement */
            $return.=$objItem->generate();
        }
        $return.='</div></div>';
        echo $return;
        return '';
    }

}
