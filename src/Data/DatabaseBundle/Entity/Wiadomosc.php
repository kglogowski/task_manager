<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Task;
use Data\DatabaseBundle\Entity\PlikWiadomosci;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wiadomosc
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\WiadomoscRepository")
 */
class Wiadomosc {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="Uzytkownik",inversedBy="wiadomosci")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $uzytkownik;

    /**
     * @var Task
     *
     * @ORM\ManyToOne(targetEntity="Task",inversedBy="wiadomosci")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $task;

    /**
     * @var string
     *
     * @ORM\Column(name="tresc", type="text")
     */
    private $tresc;

    /**
     *
     * @ORM\Column(name="numer", type="integer") 
     */
    private $numer;

    /**
     * @ORM\OneToMany(targetEntity="PlikWiadomosci", mappedBy="wiadomosc")
     */
    protected $plikiWiadomosci;

    public function __construct() {
        $this->plikiWiadomosci = new ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Wiadomosc
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
     * @return Wiadomosc
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

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
     * Set uzytkownik
     *
     * @param integer $uzytkownik
     * @return Wiadomosc
     */
    public function setUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownik = $uzytkownik;

        return $this;
    }

    /**
     * Get uzytkownikId
     *
     * @return integer 
     */
    public function getUzytkownik() {
        return $this->uzytkownik;
    }

    /**
     * Set task
     *
     * @param integer $task
     * @return Wiadomosc
     */
    public function setTask(Task $task) {
        $this->task = $task;

        return $this;
    }

    /**
     * Get taskId
     *
     * @return Task 
     */
    public function getTask() {
        return $this->task;
    }

    /**
     * Set tresc
     *
     * @param string $tresc
     * @return Wiadomosc
     */
    public function setTresc($tresc) {
        $this->tresc = $tresc;

        return $this;
    }

    /**
     * Get tresc
     *
     * @return string 
     */
    public function getTresc() {
        return $this->tresc;
    }

    public function addPlikWiadomosci(PlikWiadomosci $plikWiadomosci) {
        $this->plikiWiadomosci->add($plikWiadomosci);
        return $this;
    }

    public function getPlikiWiadomosci() {
        return $this->plikiWiadomosci;
    }

    public function setNumer($numer) {
        $this->numer = $numer;
        return $this;
    }

    public function getNumer() {
        return $this->numer;
    }

    public function canBeDelete() {
        $max = $this->getId();
        $task = $this->getTask();
        $wiadomosci = $task->getWiadomosci();
        foreach ($wiadomosci as $w) {
            if ($w->getId() > $max) {
                return false;
            }
        }
        return true;
    }

    public function delete($m) {
        $task = $this->getTask();
        $projektId = $task->getProjekt()->getId();
        $taskId = $task->getId();
        foreach ($this->getPlikiWiadomosci() as $pw) {
            /* @var $pw PlikWiadomosci */
            $dirname = $_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_wiadomosci/' . $projektId . '/' . $taskId . '/' . $this->getId();
            $filename = $dirname . '/' . $pw->getId();
            if (is_file($filename)) {
                unlink($filename);
            }
            $m->remove($pw);
        }
        if (isset($dirname)) {
            if (is_dir($dirname)) {
                rmdir($dirname);
            }
        }
        $m->remove($this);
        $m->flush();
        return true;
    }

}
