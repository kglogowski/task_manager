<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;

class RachunekController extends TmController {

    public function indexAction() {
        return $this->render('AppFrontendBundle:Rachunek:index.html.twig');
    }

}
