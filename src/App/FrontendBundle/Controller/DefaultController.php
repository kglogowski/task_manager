<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\Task;

class DefaultController extends TmController {

    public function indexAction() {
        $m = $this->getDoctrine()->getManager();
        $collMyTask = $m->getRepository('DataDatabaseBundle:Task')->findByAktualnyUzytkownik($this->getUser());
        return $this->render('AppFrontendBundle:Default:index.html.twig', array(
            'collMyTask' => $collMyTask
        ));
    }

}
