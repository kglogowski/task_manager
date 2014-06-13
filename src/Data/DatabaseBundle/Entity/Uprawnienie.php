<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\BlokUprawnien;
use Data\DatabaseBundle\Entity\Grupa;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Uprawnienie
 *
 * @ORM\Table(name="uprawnienia")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\UprawnienieRepository")
 */
class Uprawnienie
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
     * @ORM\Column(name="number", type="string", length=10)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="nazwa", type="string", length=255)
     */
    private $nazwa;

    /**
     * @var BlokUprawnien
     *
     * @ORM\ManyToOne(targetEntity="BlokUprawnien",inversedBy="uprawnienia")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $blok_uprawnien; 

    /**
     * @ORM\ManyToMany(targetEntity="Grupa", inversedBy="uprawnienia", cascade={"persist"})
     * @ORM\JoinTable(name="grupy_uprawnienia",
     * joinColumns={@ORM\JoinColumn(name="uprawnienie_id", referencedColumnName="id", onDelete="CASCADE")},
     * inverseJoinColumns={@ORM\JoinColumn(name="grupa_id", referencedColumnName="id")}
     * )
     */
    private $grupy;
    
    public function __construct() {
        $this->grupy = new ArrayCollection();
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
     * Set number
     *
     * @param string $number
     * @return Uprawnienie
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set nazwa
     *
     * @param string $nazwa
     * @return Uprawnienie
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
    
    public function getBlokUprawnien() {
        return $this->blok_uprawnien;
    }

    public function setBlokUprawnien(BlokUprawnien $blok_uprawnien) {
        $this->blok_uprawnien = $blok_uprawnien;
        return $this;
    }

    public function getGrupy() {
        return $this->grupy;
    }

    public function addGrupa(Grupa $grupa) {
        $this->grupy->add($grupa);
    }
    
    public function removeGrupa(Grupa $grupa) {
        $this->grupy->removeElement($grupa);
    }


}
