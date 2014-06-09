<?php

namespace App\FrontendBundle\Lib\Form;

use App\LibBundle\Base\BaseForm;
use Data\DatabaseBundle\Entity\Task;

class WiadomoscForm extends BaseForm {

    private $task;
    private $m;

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $m
     * @param \Data\DatabaseBundle\Entity\Task $task
     */
    public function __construct($m, Task $task) {
        $this->m = $m;
        $this->task = $task;
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $m = $this->getManager();
        $task = $this->getTask();
        $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
        $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());
        $arrUzytkownicyToDropDown = array('_vns_' => 'Aktualny: ' . $aktualny->getLogin() . ' ') + $task->getUzytkownicyToDropdown();
        $builder->setMethod('POST');
        $builder
                ->add('tekst', 'textarea', array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'bbcode',
                        'placeholder' => 'Napisz wiadomość',
                        'title' => 'Napisz wiadomość',
                    )
                ))
                ->add('aktualny', 'choice', array(
                    'choices' => $arrUzytkownicyToDropDown,
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Przypnij zadanie na:',
                    )
                ))
                ->add('status', 'choice', array(
                    'choices' => array('_vns_' => 'Aktualny status: ' . $task->getStatusLabel() . ' ') + Task::GetStatusyForDropDown(),
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Ustaw status',
                    )
                ))
                ->add('pliki', 'file', array(
                    'required' => false,
                    'attr' => array(
                        'multiple' => 'multiple',
                        'id' => 'files'
                    )
                ))
                ->add('save', 'submit', array(
                    'label' => 'Zapisz wiadomość',
                    'attr' => array(
                        'class' => 'btn btn-success'
                    )
        ));
    }

    public function getName() {
        return 'wiadomosc_form';
    }

    public function getTask() {
        return $this->task;
    }

    public function getManager() {
        return $this->m;
    }

}
