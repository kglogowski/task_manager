<?php

namespace Data\DatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Data\DatabaseBundle\Entity\Task;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Data\DatabaseBundle\Entity\PlikWiadomosci;

/**
 * PlikTask
 *
 * @ORM\Table(name="taski_pliki")
 * @ORM\Entity(repositoryClass="Data\DatabaseBundle\Entity\PlikTaskRepository")
 */
class PlikTask
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
     * @var Wiadomosc
     *
     * @ORM\ManyToOne(targetEntity="Task",inversedBy="plikiTask")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $task;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="typ", type="string", length=100, nullable=true)
     */
    private $typ;


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
     * Set task
     *
     * @param string $task
     * @return PlikTask
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return string 
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return PlikTask
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
     * Set typ
     *
     * @param string $typ
     * @return PlikTask
     */
    public function setTyp($typ)
    {
        $this->typ = $typ;

        return $this;
    }

    /**
     * Get typ
     *
     * @return string 
     */
    public function getTyp()
    {
        return $this->typ;
    }
    
    public function move(UploadedFile $plik) {
        $task = $this->getTask();
        $projectId = $task->getProjekt()->getId();
        $plik->move($_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_task/' . $projectId . '/' . $task->getId() . '/', $this->getId());
    }
    
    public function getLabelTyp() {
        return PlikWiadomosci::GetLabelTypByKey($this->getTyp());
    }
}
