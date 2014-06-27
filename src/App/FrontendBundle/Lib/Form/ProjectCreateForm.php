<?php

namespace App\FrontendBundle\Lib\Form;

use App\LibBundle\Base\BaseForm;
use Data\DatabaseBundle\Entity\UzytkownikRepository;

class ProjectCreateForm extends BaseForm {

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
                ->add('lider', 'choice', array(
                    'choices' => array('_vns_' => 'Wybierz lidera projektu') + $arrayCh,
                    'label' => 'Lider: ',
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Wybierz lidera projektu',
                    ))
                )
                ->add('nazwa', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Nazwa projektu',
                        'class' => 'form-control'
                    ))
                )
                ->add('nadawca_nazwa', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Nadawca projektu',
                        'class' => 'form-control'
                    ))
                )
                ->add('nadawca_nr_tel', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Nadawca telefon',
                        'class' => 'form-control'
                    ))
                )
                ->add('date_to', 'date', array(
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'attr' => array(
                        'class' => 'form-control date_to',
                        'placeholder' => 'Podaj termin'
                    ))
                )
                ->add('price', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Kwota netto za wykonanie projektu',
                        'class' => 'form-control'
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
        return 'project_create_form';
    }

}
