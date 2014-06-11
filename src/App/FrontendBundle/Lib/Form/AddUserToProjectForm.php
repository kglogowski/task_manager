<?php

namespace App\FrontendBundle\Lib\Form;

use App\LibBundle\Base\BaseForm;
use Data\DatabaseBundle\Entity\UzytkownikRepository;

class AddUserToProjectForm extends BaseForm {

    private $m;
    private $projekt;

    public function __construct($m, $projekt) {
        $this->m = $m;
        $this->projekt = $projekt;
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $projekt = $this->getProjekt();
        $arrayCh = array();
        $collUsers = $this->getManager()->getRepository('DataDatabaseBundle:Uzytkownik')->findBy(array(), array('id' => 'ASC'));
        $arrChoicedUsers = array();
        foreach ($collUsers as $user) {
            $arrayCh[$user->getId()] = $user->getLogin();
            $collUp = $user->getUzytkownicyProjekty();
            foreach ($collUp as $up) {
                if($up->getProjekt()->getId() == $projekt->getId()) {
                    $arrChoicedUsers[] = $user->getId();
                }
            }
        }
        $builder->add('uzytkownicy', 'choice', array(
                    'multiple' => true,
                    'choices' => $arrayCh,
                    'preferred_choices' => $arrChoicedUsers,
                    'data' => $arrChoicedUsers,
                    'label' => 'Użytkownicy: ',
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Wybierz użytkowników do projektu'
                    ))
                )
                ->add('save', 'submit', array(
                    'label' => 'Zaktualizuj użytkowników',
                    'attr' => array(
                        'class' => 'btn btn-danger'
                    ))
                )
        ;
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager;
     */
    public function getManager() {
        return $this->m;
    }

    public function getProjekt() {
        return $this->projekt;
    }

    public function getName() {
        return 'add_user_to_project_form';
    }

}
