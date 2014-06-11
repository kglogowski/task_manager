<?php

namespace App\FrontendBundle\Lib\Form;

use App\LibBundle\Base\BaseForm;
use Data\DatabaseBundle\Entity\Wiadomosc;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\Task;

class WiadomoscEditForm extends BaseForm {

    private $objWiadomosc;
    private $m;

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $m
     * @param \Data\DatabaseBundle\Entity\Wiadomosc $objWiadomosc
     */
    public function __construct($m, Wiadomosc $objWiadomosc) {
        $this->m = $m;
        $this->objWiadomosc = $objWiadomosc;
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $m = $this->getManager();
        $objWiadomosc = $this->getWiadomosc();
        $task = $objWiadomosc->getTask();
        $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
        $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());
        $arrUzytkownicyToDropDown = array('_vns_' => 'Aktualny: ' . $aktualny->getLogin() . ' ') + $task->getUzytkownicyToDropdown();
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
                ->add('save', 'submit', array(
                    'label' => 'Zapisz wiadomość',
                    'attr' => array(
                        'class' => 'btn btn-success'
                    )
        ));
    }

    public function getName() {
        return 'wiadomosc_edit_form';
    }

    public function getWiadomosc() {
        return $this->objWiadomosc;
    }

    public function getManager() {
        return $this->m;
    }

}
