<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends Controller{
    
        public function showAllUsersAction() {
            $m = $this->getDoctrine()->getManager();
            $repo = $m->getRepository('DataDatabaseBundle:Uzytkownik');
            $users = $repo->findAll();
            
        return $this->render('AppFrontendBundle:Users:show.html.twig',
                array('Users' =>  $users));
    }
}
