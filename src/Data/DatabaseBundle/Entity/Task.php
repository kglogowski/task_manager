<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Uzytkownik;
use Data\DatabaseBundle\Entity\Wiadomosc;
use Doctrine\Common\Collections\ArrayCollection;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\PlikTask;

/**
 * Task
 *
 * @ORM\Table(name="taski")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\TaskRepository")
 */
class Task {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="priorytet", type="smallint")
     */
    private $priorytet;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_zakonczenia", type="datetime", nullable=true)
     */
    private $dataZakonczenia;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="termin", type="datetime", nullable=true)
     */
    private $termin;

    /**
     * @var integer
     *
     * @ORM\Column(name="aktualny_uzytkownik", type="integer")
     */
    private $aktualnyUzytkownik;

    /**
     * @var integer
     *
     * @ORM\Column(name="poprzedni_uzytkownik", type="integer", nullable=true)
     */
    private $poprzedniUzytkownik;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="creator", type="integer")
     */
    private $creator;

    /**
     * @var text
     *
     * @ORM\Column(name="opis", type="text")
     */
    private $opis;

    /**
     * @ORM\ManyToMany(targetEntity="Uzytkownik", inversedBy="tasks", cascade={"persist"})
     * @ORM\JoinTable(name="uzytkownicy_taski",
     * joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="uzytkownik_id", referencedColumnName="id")}
     * )
     */
    private $uzytkownicy;

    /**
     * @ORM\OneToMany(targetEntity="Wiadomosc", mappedBy="task")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $wiadomosci;

    /**
     * @ORM\OneToMany(targetEntity="PlikTask", mappedBy="task")
     */
    protected $plikiTask;
    
    /**
     * @ORM\ManyToOne(targetEntity="Projekt",inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $projekt;

    CONST STATUS_NOWY = 1;
    CONST STATUS_REALIZOWANY = 2;
    CONST STATUS_ZATRZYMANY = 3;
    CONST STATUS_TESTOWANY = 4;
    CONST STATUS_TEST_NEGATYWNY = 5;
    CONST STATUS_TEST_POZYTWYNY = 6;
    CONST STATUS_WGRYWANY = 7;
    CONST STATUS_WGRANY = 8;
    CONST STATUS_ZAMKNIETY = 9;
    CONST STATUS_PRZYWROCONY = 10;

    protected static $arrStatusLabel = array(
        self::STATUS_NOWY => 'Nowy',
        self::STATUS_REALIZOWANY => 'W realizacji',
        self::STATUS_ZATRZYMANY => 'Zatrzymany',
        self::STATUS_TESTOWANY => 'Testowany',
        self::STATUS_TEST_NEGATYWNY => 'Test negatywny',
        self::STATUS_TEST_POZYTWYNY => 'Test pozytywny',
        self::STATUS_WGRYWANY => 'Wgrywany',
        self::STATUS_WGRANY => 'Wgrany',
        self::STATUS_ZAMKNIETY => 'Zamknięty',
        self::STATUS_PRZYWROCONY => 'Przywrocony',
    );
    protected static $arrStatusClass = array(
        self::STATUS_NOWY => 'status_nowy',
        self::STATUS_REALIZOWANY => 'status_w_realizacji',
        self::STATUS_ZATRZYMANY => 'status_zatrzymany',
        self::STATUS_TESTOWANY => 'status_testowany',
        self::STATUS_TEST_NEGATYWNY => 'status_test_negatywny',
        self::STATUS_TEST_POZYTWYNY => 'status_test_pozytywny',
        self::STATUS_WGRYWANY => 'status_wgrywany',
        self::STATUS_WGRANY => 'status_wgrany',
        self::STATUS_ZAMKNIETY => 'status_zamknięty',
        self::STATUS_PRZYWROCONY => 'status_przywrocony',
    );

    public static function GetStatusyForDropDown() {
        return self::$arrStatusLabel;
    }

    public function getStatusLabelByKey($key) {
        return self::$arrStatusLabel[$key];
    }

    public function getStatusLabel() {
        return self::$arrStatusLabel[$this->getStatus()];
    }

    public function getStatusClass() {
        return self::$arrStatusClass[$this->getStatus()];
    }

    CONST PRIORYTET_BARDZO_NISKI = 1;
    CONST PRIORYTET_NISKI = 2;
    CONST PRIORYTET_SREDNI = 3;
    CONST PRIORYTET_WYSOKI = 4;
    CONST PRIORYTET_BARDZO_WYSOKI = 5;

    protected static $arrPriorytetLabel = array(
        self::PRIORYTET_BARDZO_NISKI => 'bardzo niski',
        self::PRIORYTET_NISKI => 'niski',
        self::PRIORYTET_SREDNI => 'średni',
        self::PRIORYTET_WYSOKI => 'wysoki',
        self::PRIORYTET_BARDZO_WYSOKI => 'bardzo wysoki',
    );
    protected static $arrPriorytetClass = array(
        self::PRIORYTET_BARDZO_NISKI => 'priorytet_bniski',
        self::PRIORYTET_NISKI => 'priorytet_niski',
        self::PRIORYTET_SREDNI => 'priorytet_sredni',
        self::PRIORYTET_WYSOKI => 'priorytet_wysoki',
        self::PRIORYTET_BARDZO_WYSOKI => 'priorytet_bwysoki',
    );

    public static function GetProtytety() {
        return self::$arrPriorytetLabel;
    }

    public function getPriorytetClassByKey($key) {
        return self::$arrPriorytetClass[$key];
    }

    public function getPriorytetClass() {
        return self::$arrPriorytetClass[$this->getPriorytet()];
    }

    public function getPriorytetLabel() {
        return self::$arrPriorytetLabel[$this->getPriorytet()];
    }

    public function __construct() {
        $this->plikiTask = new ArrayCollection();
        $this->uzytkownicy = new ArrayCollection();
        $this->wiadomosci = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return Task
     */
    public function setLabel($label) {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Task
     */
    public function setCreatedAt() {
        $this->createdAt = new \DateTime('now');

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Task
     */
    public function setUpdatedAt() {
        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Task
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus() {
        return $this->status;
    }

    public static function GetStatusyKoncowe() {
        return array(
            self::STATUS_ZAMKNIETY
        );
    }

    public function isZakonczony() {
        return in_array($this->getStatus(), Task::GetStatusyKoncowe());
    }

    /**
     * Set dataZakonczenia
     *
     * @param \DateTime $dataZakonczenia
     * @return Task
     */
    public function setDataZakonczenia($dataZakonczenia) {
        $this->dataZakonczenia = $dataZakonczenia;

        return $this;
    }

    /**
     * Get dataZakonczenia
     *
     * @return \DateTime 
     */
    public function getDataZakonczenia() {
        return $this->dataZakonczenia;
    }

    /**
     * Set aktualnyUzytkownik
     *
     * @param integer $aktualnyUzytkownik
     * @return Task
     */
    public function setAktualnyUzytkownik($aktualnyUzytkownik) {
        $this->aktualnyUzytkownik = $aktualnyUzytkownik;

        return $this;
    }

    /**
     * Get aktualnyUzytkownik
     *
     * @return integer 
     */
    public function getAktualnyUzytkownik() {
        return $this->aktualnyUzytkownik;
    }

    /**
     * Get poprzedniUzytkownik
     *
     * @return integer 
     */
    public function getPoprzedniUzytkownik() {
        return $this->poprzedniUzytkownik;
    }

    /**
     * 
     * @param type integer $poprzedniUzytkownik
     * @return Task
     */
    public function setPoprzedniUzytkownik($poprzedniUzytkownik) {
        $this->poprzedniUzytkownik = $poprzedniUzytkownik;
        return $this;
    }

        
    public function getUzytkownicy() {
        return $this->uzytkownicy->toArray();
    }

    public function getUzytkownicyToDropdown() {
        $uzytkownicy = array();
        foreach ($this->getUzytkownicy() as $user) {
            $uzytkownicy[$user->getId()] = $user->getLogin();
        }
        return $uzytkownicy;
    }

    public function addUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownicy->add($uzytkownik);
    }

    public function removeUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownicy->removeElement($uzytkownik);
    }

    public function getWiadomosci() {
        return $this->wiadomosci->toArray();
    }
        public function getMessage() {
        return $this->wiadomosci;
    }

    public function addWiadomosc(Wiadomosc $uzytkownik) {
        $this->wiadomosci->add($uzytkownik);
    }

    public function removeWiadomosc(Wiadomosc $uzytkownik) {
        $this->wiadomosci->removeElement($uzytkownik);
    }

    public function getPriorytet() {
        return $this->priorytet;
    }

    public function getTermin() {
        return $this->termin;
    }

    public function setPriorytet($priorytet) {
        $this->priorytet = $priorytet;
        return $this;
    }

    public function setTermin(\DateTime $termin) {
        $this->termin = $termin;
        return $this;
    }

    /**
     * 
     * @return Projekt
     */
    public function getProjekt() {
        return $this->projekt;
    }

    public function setProjekt(Projekt $projekt) {
        $this->projekt = $projekt;
        return $this;
    }

    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($creator) {
        $this->creator = $creator;
    }

    public function getOpis() {
        return $this->opis;
    }

    public function setOpis($opis) {
        $this->opis = $opis;
        return $this;
    }

    
    public function addPlikTask(PlikTask $plikTask) {
        $this->plikiTask->add($plikTask);
        return $this;
    }
    
    public function getPlikiTask() {
        return $this->plikiTask;
    }
    
}
