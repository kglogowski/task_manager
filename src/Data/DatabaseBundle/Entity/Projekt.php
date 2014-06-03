<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Data\DatabaseBundle\Entity\UzytkownikProjekt;
use Data\DatabaseBundle\Entity\Task;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Projekt
 *
 * @ORM\Entity
 * @UniqueEntity(fields="name", message="inny projekt korzysta z podanego adresu url.")
 * @ORM\Table(name="projekty")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\ProjektRepository")
 */
class Projekt
{
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, unique=true)
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
     * @var \DateTime
     *
     * @ORM\Column(name="data_zakonczenia", type="datetime", nullable=true)
     */
    private $dataZakonczenia;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nadawca_nazwa", type="string", length=255, nullable=true)
     */
    private $nadawcaNazwa;

    /**
     * @var string
     *
     * @ORM\Column(name="nadawca_telefon", type="string", length=15, nullable=true)
     */
    private $nadawcaTelefon;

    /**
     * @var integer
     *
     * @ORM\Column(name="creator", type="integer")
     */
    private $creator;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="termin", type="datetime", nullable=true)
     */
    private $termin;
    
    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="projekt")
     */
    private $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="UzytkownikProjekt", mappedBy="projekt")
     */
    private $uzytkownicy_projekty;
    
     /**
     * @ORM\Column(type="boolean", nullable=true )
     */
    public $skasowane;

    public function __construct() {
        $this->uzytkownicy_projekty = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }
    
    CONST STATUS_SPECYFIKACJA = 1;
    CONST STATUS_PROJEKTOWANIE = 2;
    CONST STATUS_IMPLEMENTACJA = 3;
    CONST STATUS_INTEGRACJA = 4;
    CONST STATUS_DEKORACJA = 5;
    CONST STATUS_ZAMKNIETY = 6;
    
    protected static $arrStatusLabel = array(
        self::STATUS_SPECYFIKACJA    =>  'Specyfikacja',
        self::STATUS_PROJEKTOWANIE    =>  'Projektowanie',
        self::STATUS_IMPLEMENTACJA    =>  'Implementacja',
        self::STATUS_INTEGRACJA    =>  'Integracja',
        self::STATUS_DEKORACJA    =>  'Dekoracja',
        self::STATUS_ZAMKNIETY    =>  'Zamknięty',
    );
    
    public static function GetStatusyKoncowe() {
        return array(
            self::STATUS_ZAMKNIETY
        );
    }

    public function isZakonczony() {
        return in_array($this->getStatus(), Task::GetStatusyKoncowe());
    }

        public static function GetStatusy(){ 
    return self::$arrStatusLabel; 
    }

    public function getStatusLabelByKey($key) {
        return self::$arrStatusLabel[$key];
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Projekt
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return Projekt
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Projekt
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime('now');

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Projekt
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set dataZakonczenia
     *
     * @param \DateTime $dataZakonczenia
     * @return Projekt
     */
    public function setDataZakonczenia($date)
    {
        $this->dataZakonczenia = new \DateTime('now');
        return $this;
    }

    /**
     * Get dataZakonczenia
     *
     * @return \DateTime 
     */
    public function getDataZakonczenia()
    {
        return $this->dataZakonczenia == null;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Projekt
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getNadawcaNazwa() {
        return $this->nadawcaNazwa;
    }

    public function getNadawcaTelefon() {
        return $this->nadawcaTelefon;
    }

    public function getTermin() {
        return $this->termin;
    }

    public function setNadawcaNazwa($nadawcaNazwa) {
        $this->nadawcaNazwa = $nadawcaNazwa;
        return $this;
    }

    public function setNadawcaTelefon($nadawcaTelefon) {
        $this->nadawcaTelefon = $nadawcaTelefon;
        return $this;
    }

    public function setTermin(\DateTime $termin) {
        $this->termin = $termin;
        return $this;
    }

        
    
    /**
     * Add uzytkownicy_projekty
     *
     * @param \Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty
     * @return Projekt
     */
    public function addUzytkownicyProjekty(\Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty)
    {
        $this->uzytkownicy_projekty->add($uzytkownicyProjekty);

        return $this;
    }

    /**
     * Remove uzytkownicy_projekty
     *
     * @param \Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty
     */
    public function removeUzytkownicyProjekty(\Data\DatabaseBundle\Entity\UzytkownikProjekt $uzytkownicyProjekty)
    {
        $this->uzytkownicy_projekty->removeElement($uzytkownicyProjekty);
    }

    /**
     * Get uzytkownicy_projekty
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUzytkownicyProjekty()
    {
        return $this->uzytkownicy_projekty;
    }
    
    /**
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks() {
        return $this->tasks;
    }
    
    public function getActiveTasks() {
        $tasks = $this->tasks;
        $arrTasks = array();
        foreach ($tasks as $task) {
            if(!$task->isZakonczony()) {
                $arrTasks[] = $task;
            }
        }
        return $arrTasks;
    }

    public function removeTask(Task $task) {
        $this->tasks->removeElement($task);
    }
    
    public function addTask(Task $task) {
        $this->tasks->add($task);
    }

    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($creator) {
        $this->creator = $creator;
    }
    
    public function getTimeToFinish()  {
        $now = new \DateTime('now');
        return $this->getTermin() < $now ? 'przekroczono czas' : $this->getTermin()->diff($now)->days;
    }

    /**
     * Set skasowane
     *
     * @param boolean $skasowane
     * @return Projekt
     */
    public function setSkasowane($skasowane)
    {
        $this->skasowane = $skasowane;

        return $this;
    }

    /**
     * Get skasowane
     *
     * @return boolean 
     */
    public function getSkasowane()
    {
        return $this->skasowane;
    }
}
