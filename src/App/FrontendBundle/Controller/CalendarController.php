<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CalendarController extends Controller {
    
    public function indexAction($year, $month) {
        $m = $this->getDoctrine()->getManager();
        $objBrowser = new \App\FrontendBundle\Lib\Browser\Calendar($year, $month, $this->getUser(), $this);
        return $this->render('AppFrontendBundle:Calendar:index.html.twig', array(
            'objBrowser' => $objBrowser,
            'year' => $year,
            'month' => $month,
        ));
    }
}
