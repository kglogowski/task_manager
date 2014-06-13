<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Uzytkownik;
use Doctrine\Common\Collections\ArrayCollection;
use Data\DatabaseBundle\Entity\Uprawnienie;

/**
 * Grupa
 *
 * @ORM\Table(name="grupy")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\GrupaRepository")
 */
class Grupa
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
     * @ORM\Column(name="nazwa", type="string", length=255)
     */
    private $nazwa;

    /**
     * @var string
     *
     * @ORM\Column(name="klasa", type="string", length=255)
     */
    private $klasa;

    
    /**
     * @ORM\OneToMany(targetEntity="Uzytkownik", mappedBy="grupa")
     */
    private $uzytkownicy;

    /**
     * @ORM\ManyToMany(targetEntity="Uprawnienie", mappedBy="grupy")
     */
    private $uprawnienia;
    
    
    public function __construct() {
        $this->uzytkownicy = new ArrayCollection();
        $this->uprawnienia = new ArrayCollection();
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
     * Set nazwa
     *
     * @param string $nazwa
     * @return Grupa
     */
    public function setNazwa($nazwa)
    {
        $this->nazwa = $nazwa;

        return $this;
    }

    /**
     * Get nazwa
     *
     * @return string 
     */
    public function getNazwa()
    {
        return $this->nazwa;
    }

    /**
     * Set klasa
     *
     * @param string $klasa
     * @return Grupa
     */
    public function setKlasa($klasa)
    {
        $this->klasa = $klasa;

        return $this;
    }

    /**
     * Get klasa
     *
     * @return string 
     */
    public function getKlasa()
    {
        return $this->klasa;
    }
    
    public function getUzytkownicy() {
        return $this->uzytkownicy;
    }
    
    public function getUzytkownicyArray() {
        return $this->uzytkownicy->toArray();
    }

    public function addUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownicy->add($uzytkownik);
    }
    
    public function removeUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownicy->removeElement($uzytkownik);
    }

    public function getUprawnienia() {
        return $this->uprawnienia;
    }
    
    public function getUprawnieniaArray() {
        return $this->uprawnienia->toArray();
    }

    public function addUprawnienie(Uprawnienie $uprawnienie) {
        $this->uprawnienia->add($uprawnienie);
    }
    
    public function removeUprawnienie(Uprawnienie $uprawnienie) {
        $this->uprawnienia->removeElement($uprawnienie);
    }


}
