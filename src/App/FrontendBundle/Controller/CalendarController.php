<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CalendarController extends Controller {
    
    public function indexAction() {
        return $this->render('AppFrontendBundle:Calendar:index.html.twig');
    }
}
