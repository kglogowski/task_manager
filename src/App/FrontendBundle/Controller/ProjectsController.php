<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\UzytkownikProjekt;

class ProjectsController extends TmController {
    
    public function indexAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();
        
        $collMyProjekt = $m->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.status != :status_zamkniety
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
          ->getResult();
        
        return $this->render('AppFrontendBundle:Projects:index.html.twig', array(
            'myProjects' => $collMyProjekt,
            'UzytkownikProjekt'    =>  new UzytkownikProjekt(),
            'Projekt'    =>  new Projekt()
        ));
    }
    
    public function newAction() {
        $m = $this->getDoctrine()->getManager();
        $form = $this->createForm(new \App\FrontendBundle\Lib\Form\ProjectCreateForm($m));
        
        $request = $this->getRequest();
        
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $data = $form->getData();
                $link = $this->createLinkFromLabel($data['nazwa']);
                if($m->getRepository('DataDatabaseBundle:Projekt')->findOneByName($link) instanceof Projekt) {
                    $this->get('session')->getFlashBag()->set('error', 'Występuje już projekt o podanej nazwie');
                    return $this->redirect($this->generateUrl('projects_new'));
                }
                $arrUP = array();
                $arrUzytkownicy = $m->createQuery("
                    SELECT u FROM DataDatabaseBundle:Uzytkownik u WHERE u in (".  join(',', $data['uzytkownicy']).")
                ")->getResult();
                foreach ($arrUzytkownicy as $uzytkownik) {
                    $u_id = $uzytkownik->getId();
                    $arrUP[$u_id] = new UzytkownikProjekt();
                    $arrUP[$u_id]->setUzytkownik($uzytkownik);
                    if($u_id == $data['lider']) {
                        $arrUP[$u_id]->setRola(UzytkownikProjekt::ROLA_LIDER);
                    } else {
                        $arrUP[$u_id]->setRola(UzytkownikProjekt::ROLA_POMOCNIK);
                    }
                }
                $newProjekt = new Projekt();
                $newProjekt
                        ->setCreatedAt()
                        ->setTermin($data['date_to'])
                        ->setUpdatedAt()
                        ->setLabel($data['nazwa'])
                        ->setName($link)
                        ->setNadawcaNazwa($data['nadawca_nazwa'])
                        ->setNadawcaTelefon($data['nadawca_nr_tel'])
                        ->setStatus(Projekt::STATUS_SPECYFIKACJA);
                $m->persist($newProjekt);
                foreach ($arrUP as $up) {
                    $up->setProjekt($newProjekt);
                    $m->persist($up);
                }
                $m->flush();
                $this->redirectWithFlash('projects','Stworzono nowy projekt');    
            }
        }
        
        return $this->render('AppFrontendBundle:Projects:new.html.twig', array('form' => $form->createView()));
    }
}
