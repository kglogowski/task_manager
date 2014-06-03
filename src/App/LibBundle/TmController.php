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
    
    public function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
    
}
