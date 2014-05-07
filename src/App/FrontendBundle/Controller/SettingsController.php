<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingsController extends Controller {
    
    public function indexAction() {
        return $this->render('AppFrontendBundle:Settings:index.html.twig');
    }
}
