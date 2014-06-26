<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Uprawnienie;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BlokUprawnien
 *
 * @ORM\Table(name="uprawnienia_bloki")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\BlokUprawnienRepository")
 */
class BlokUprawnien
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
     * @ORM\Column(name="identyfikator", type="string", length=5)
     */
    private $identyfikator;
    
    /**
     * @ORM\OneToMany(targetEntity="Uprawnienie", mappedBy="blok_uprawnien")
     */
    private $uprawnienia;

    public function __construct() {
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
     * @return BlokUprawnien
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
    
    public function getUprawnienia() {
        return $this->uprawnienia;
    }

    public function addUprawnienie(Uprawnienie $uprawnienie) {
        $this->uprawnienia->add($uprawnienie);
    }
    
    public function removeUprawnienie(Uprawnienie $uprawnienie) {
        $this->uprawnienia->removeElement($uprawnienie);
    }
    
    public function getIdentyfikator() {
        return $this->identyfikator;
    }

    public function setIdentyfikator($identyfikator) {
        $this->identyfikator = $identyfikator;
        return $this;
    }


}
