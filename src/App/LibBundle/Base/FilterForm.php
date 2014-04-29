<?php

namespace App\LibBundle\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class FilterForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
    }

    public function createQuery() {
        
    }

    abstract public function getName();
}
