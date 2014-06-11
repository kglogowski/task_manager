<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\Uzytkownik;

/**
 * UzytkownikProjekt
 *
 * @ORM\Table(name="uzytkownicy_projekty")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\UzytkownikProjektRepository")
 */
class UzytkownikProjekt {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Uzytkownik",inversedBy="uzytkownicy_projekty")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $uzytkownik;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Projekt",inversedBy="uzytkownicy_projekty")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $projekt;

    /**
     * @var integer
     *
     * @ORM\Column(name="rola", type="smallint")
     */
    private $rola;

    CONST ROLA_LIDER = 1;
    CONST ROLA_POMOCNIK = 2;

    protected static $arrRoleLabel = array(
        self::ROLA_LIDER => 'Lider',
        self::ROLA_POMOCNIK => 'Pomocnik'
    );

    public function getRolaLabelByKey($key) {
        return self::$arrRoleLabel[$key];
    }

    public function getRolaLabel() {
        return self::$arrRoleLabel[$this->getRola()];
    }

    public static function GetRoleArray() {
        return self::$arrRoleLabel;
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
     * Set uzytkownikId
     *
     * @param integer $uzytkownikId
     * @return UzytkownikProjekt
     */
    public function setUzytkownik(Uzytkownik $uzytkownik) {
        $this->uzytkownik = $uzytkownik;

        return $this;
    }

    /**
     * Get uzytkownikId
     *
     * @return Uzytkownik 
     */
    public function getUzytkownik() {
        return $this->uzytkownik;
    }

    /**
     * Set projekt
     *
     * @param integer $projekt
     * @return UzytkownikProjekt
     */
    public function setProjekt(Projekt $projekt) {
        $this->projekt = $projekt;

        return $this;
    }

    /**
     * Get projekt
     *
     * @return Projekt 
     */
    public function getProjekt() {
        return $this->projekt;
    }

    /**
     * Set rola
     *
     * @param integer $rola
     * @return UzytkownikProjekt
     */
    public function setRola($rola) {
        $this->rola = $rola;

        return $this;
    }

    /**
     * Get rola
     *
     * @return integer 
     */
    public function getRola() {
        return $this->rola;
    }

}
