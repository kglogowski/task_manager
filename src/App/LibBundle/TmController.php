<?php

namespace App\LibBundle;

class TmController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller {

    public function createLinkFromLabel($label) {
        return iconv('LATIN2', 'UTF8', str_replace(' ', '-', strtolower(
                                strtr(strtr($label, "ś", "s"), "ź", "ż")
                        )
        ));
    }

    public function redirectWithFlash($route, $description, $type = 'success', $args = array()) {
        $this->get('session')->getFlashBag()->set($type, $description);
        return $this->redirect($this->generateUrl($route, $args));
    }

    /**
     * 
     * @param \Data\DatabaseBundle\Entity\Projekt $projekt
     * @param \Data\DatabaseBundle\Entity\Uzytkownik $uzytkownik
     * @return type
     */
    public function isLider($projekt, $uzytkownik = null) {
        $uzytkownik = $uzytkownik != null ? $uzytkownik : $this->getUser();
        return $this->getDoctrine()->getManager()->getRepository('DataDatabaseBundle:UzytkownikProjekt')->findByProjektAndUzytkownik($projekt, $uzytkownik)->getRola() == \Data\DatabaseBundle\Entity\UzytkownikProjekt::ROLA_LIDER ? true : false;
    }

    public function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
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

    public function sendMailInfo($collUsers, $subject, $body) {
        if ($this->container->hasParameter('mailer_available') && $this->container->getParameter('mailer_available') === TRUE) {
            $arrTo = array();
            foreach ($collUsers as $objUser) {
                /* @var $objUser \Data\DatabaseBundle\Entity\Uzytkownik  */
                $arrTo[] = $objUser->getEmail();
            }
            $from = array($this->container->getParameter('mailer_user') => 'Task manager');
            $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($from)
                    ->setTo($arrTo)
                    ->setBody($body)
                    ->setContentType("text/html");
            $this->get('mailer')->send($message);
        }
        return TRUE;
    }

}
