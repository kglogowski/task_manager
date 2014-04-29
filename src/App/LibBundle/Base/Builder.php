<?php

namespace App\LibBundle\Base;

abstract class Builder {

    private $page;
    private $objFilterForm;




    public function __construct($page = 1) {
        $this->page = $page;
        $this->objFilterForm = $this->createFilterForm();
        
    }
    
    
    abstract function createFilterForm();
    
    public function getFilterForm() {
        var_dump($this->objFilterForm->get());
        return $this->objFilterForm;
    }
    
    
    
    

}
