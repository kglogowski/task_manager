<?php

namespace App\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends Controller {

    public function editAction() {
        return $this->render('AppFrontendBundle:Profile:edit.html.twig');
    }

    public function changeBasicAction(Request $request) {
//        $response = array("code" => 100, "success" => true);
//        return new Response(json_encode($response)); 
        $u = $this->getDoctrine()->getManager()->getRepository('DataDatabaseBundle:Uzytkownik')->findOneByEmail($request->get('email'));
        if ($u instanceof \Data\DatabaseBundle\Entity\Uzytkownik && $u->getId() != $this->getUser()->getId()) {
            return $this->render('AppFrontendBundle:Profile:changeBasic.html.twig', array(
                        'msg' => $request->get('msg')
            ));
        }
        $imie = $request->get('imie');
        $nazwisko = $request->get('nazwisko');
        $login = $request->get('login');
        $email = $request->get('email');
        $m = $this->getDoctrine()->getManager();
        $this->getUser()->setImie($imie);
        $this->getUser()->setNazwisko($nazwisko);
        $this->getUser()->setLogin($login);
        $this->getUser()->setEmail($email);
        $m->persist($this->getUser());
        $m->flush();
        return $this->render('AppFrontendBundle:Profile:changeBasic.html.twig', array(
                    'msg' => $request->get('msg')
        ));
    }

    public function changePasswordAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $objUser = $this->getUser();
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($objUser);
        $password = $encoder->encodePassword($request->get('s_pass'), $objUser->getSalt());
        if ($objUser->getHaslo() == $password) {
            $objUser->setHaslo($request->get('n_pass'), $this->get('security.encoder_factory'));
            $m->persist($objUser);
            $m->flush();
            echo false;
        } else {
            echo 's_pass;Błędne hasło';
        }
        return new Response();
    }

}
