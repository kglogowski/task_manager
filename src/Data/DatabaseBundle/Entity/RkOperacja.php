<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RkOperacja
 *
 * @ORM\Table(name="rk_operacje")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\RkOperacjaRepository")
 */
class RkOperacja
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
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="kwota_netto", type="decimal", scale=2)
     */
    private $kwotaNetto;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="confirm", type="boolean", options={"default" = false})
     */
    private $confirm;


    public function __construct() {
        $this->createdAt = new \DateTime("now");
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
     * Set label
     *
     * @param string $label
     * @return RkOperacja
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
     * Set kwotaNetto
     *
     * @param string $kwotaNetto
     * @return RkOperacja
     */
    public function setKwotaNetto($kwotaNetto)
    {
        $this->kwotaNetto = $kwotaNetto;

        return $this;
    }

    /**
     * Get kwotaNetto
     *
     * @return string 
     */
    public function getKwotaNetto()
    {
        return $this->kwotaNetto;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return RkOperacja
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

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
     * Set confirm
     *
     * @param boolean $confirm
     * @return RkOperacja
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Get confirm
     *
     * @return boolean 
     */
    public function getConfirm()
    {
        return $this->confirm;
    }
}
