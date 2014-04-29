<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    public function indexAction() {
        
        return $this->render('AppFrontendBundle:Default:index.html.twig');
    }

}
