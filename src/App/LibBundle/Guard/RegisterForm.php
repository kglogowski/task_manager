<?php

namespace App\LibBundle\Guard;

use App\LibBundle\Base\BaseForm;

class RegisterForm extends BaseForm {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('imie', 'text', array(
            'attr' => array(
                'placeholder' => 'Imię'
            )
        ));
        $builder->add('nazwisko', 'text', array(
            'attr' => array(
                'placeholder' => 'Nazwisko'
            )
        ));
        $builder->add('email', 'email', array(
            'attr' => array(
                'placeholder' => 'Email'
            )
        ));
        $builder->add('haslo', 'repeated', array(
            'invalid_message' => 'Hasła nie zgadzają się',
            'first_options' => array(
                'attr' => array(
                    'placeholder' => 'Hasło'
                )
            ),
            'second_options' => array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Powtórz hasło'
                )
            ),
            'type' => 'password',
                )
        );
        $builder->add('register', 'submit', array('label' => 'Zarejestruj się'));
        $builder->add('token', 'hidden', array(
            'data' => 'jsidjsaidsa8dasdu8238jdew8dj8aesdc',
        ));
    }

    public function getName() {
        return 'register_form';
    }

}
