<?php

namespace App\LibBundle;

class TmController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller {

    public function createLinkFromLabel($label) {
        return iconv('LATIN2','UTF8',str_replace(' ', '-', strtolower(
                        strtr(strtr($label, "ś", "s"), "ź", "ż")
                )
        ));
    }
    
    public function redirectWithFlash($route, $description, $type='success', $args = array()) {
        $this->get('session')->getFlashBag()->set($type, $description);
        return $this->redirect($this->generateUrl($route, $args));
    }
    
    public function isLider($projekt, $uzytkownik = null) {
        $uzytkownik = $uzytkownik != null ? $uzytkownik : $this->getUser();
        return $this->getDoctrine()->getManager()->getRepository('DataDatabaseBundle:UzytkownikProjekt')->findByProjektAndUzytkownik($projekt, $uzytkownik)->getRola();
    }
    
}
