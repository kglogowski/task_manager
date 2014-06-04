<?php

namespace App\FrontendBundle\Lib\Form;

use App\LibBundle\Base\BaseForm;
use Data\DatabaseBundle\Entity\UzytkownikRepository;


class AddUserToProjectForm extends BaseForm{
        private $m;

    public function __construct($m) {
        $this->m = $m;
    }
    
       public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $arrayCh = array();
        $collUsers = $this->getManager()->getRepository('DataDatabaseBundle:Uzytkownik')->findBy(array(), array('id' => 'ASC'));
        foreach ($collUsers as $user) {
            $arrayCh[$user->getId()] = $user->getLogin();
        }
        $builder->add('uzytkownicy', 'choice', array(
                    'multiple' => true,
                    'choices' => $arrayCh,
                    'label' => 'Użytkownicy: ',
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Wybierz użytkowników do projektu'
                    ))
                )

                ->add('save', 'submit', array(
                    'label' => 'Zapisz',
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

    public function getName() {
        return 'add_user_to_project_form';
    }
}
