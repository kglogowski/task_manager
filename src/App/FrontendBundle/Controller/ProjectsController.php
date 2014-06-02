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
                AND p.skasowane is null
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                ->getResult();

        return $this->render('AppFrontendBundle:Projects:index.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'UzytkownikProjekt' => new UzytkownikProjekt(),
                    'Projekt' => new Projekt()
        ));
    }
    
        public function zakonczoneAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();

        $collMyProjekt = $m->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.status = :status_zamkniety
                AND p.skasowane is null
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                ->getResult();

        return $this->render('AppFrontendBundle:Projects:zakonczone.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'UzytkownikProjekt' => new UzytkownikProjekt(),
                    'Projekt' => new Projekt()
        ));
    }
    
            public function skasowaneAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();

        $collMyProjekt = $m->createQuery("
            SELECT p.label, p.status as status_projektu, p.name, up.rola as rola_uzytkownika, p.termin as termin
                FROM DataDatabaseBundle:Projekt p
                LEFT JOIN DataDatabaseBundle:UzytkownikProjekt up WITH p.id = up.projekt
                WHERE up.uzytkownik = :uzytkownik_id
                AND p.status = :status_zamkniety
                AND p.skasowane is null
        ")->setParameter(':uzytkownik_id', $uzytkownik->getId())->setParameter(':status_zamkniety', Projekt::STATUS_ZAMKNIETY)
                ->getResult();

        return $this->render('AppFrontendBundle:Projects:zakonczone.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'UzytkownikProjekt' => new UzytkownikProjekt(),
                    'Projekt' => new Projekt()
        ));
    }

    public function newAction() {
        $m = $this->getDoctrine()->getManager();
        $form = $this->createForm(new \App\FrontendBundle\Lib\Form\ProjectCreateForm($m));

        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $link = $this->createLinkFromLabel($data['nazwa']);
                if ($m->getRepository('DataDatabaseBundle:Projekt')->findOneByName($link) instanceof Projekt) {
                    $this->get('session')->getFlashBag()->set('error', 'Występuje już projekt o podanej nazwie');
                    return $this->redirect($this->generateUrl('projects_new'));
                }
                $arrUP = array();
                $arrUzytkownicy = $m->createQuery("
                    SELECT u FROM DataDatabaseBundle:Uzytkownik u WHERE u in (" . join(',', $data['uzytkownicy']) . ")
                ")->getResult();
                foreach ($arrUzytkownicy as $uzytkownik) {
                    $u_id = $uzytkownik->getId();
                    $arrUP[$u_id] = new UzytkownikProjekt();
                    $arrUP[$u_id]->setUzytkownik($uzytkownik);
                    if ($u_id == $data['lider']) {
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
                        ->setStatus(Projekt::STATUS_SPECYFIKACJA)
                        ->setCreator($this->getUser()->getId());
                $m->persist($newProjekt);
                foreach ($arrUP as $up) {
                    $up->setProjekt($newProjekt);
                    $m->persist($up);
                }
                $m->flush();
                return $this->redirectWithFlash('projects', 'Stworzono nowy projekt');
            }
        }

        return $this->render('AppFrontendBundle:Projects:new.html.twig', array('form' => $form->createView()));
    }

    public function editRolesAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie istnieje taki projekt', 'error');
        }
        $user = $this->getUser();
        if ($user->getRoleProjektuByProjektId($projekt->getId()) != UzytkownikProjekt::ROLA_LIDER) {
            return $this->redirectWithFlash('projects', 'Musisz być liderem projektu, aby móc zarządzać rolami', 'error');
        }
        $uzytkownicy = $projektRepo->findUzytkownicyByProjekt($projekt);
        $role = UzytkownikProjekt::GetRoleArray();



        $request = $this->get('request');
        if ($request->isMethod('POST')) {
            $liders = 0;
            foreach ($request->request as $key => $param) {
                if ($param == UzytkownikProjekt::ROLA_LIDER) {
                    ++$liders;
                    if ($liders > 1) {
                        return $this->redirectWithFlash('projects_edit_roles', 'Maksymalnie może być jeden lider projektu', 'error', array(
                                    'projekt_nazwa' => $projekt->getName(),
                        ));
                    }
                }
                $u = $m->find('DataDatabaseBundle:Uzytkownik', $key);
                $u->setRoleProjektuByProjektId($projekt->getId(), $param);
                $m->persist($u);
            }
            if ($liders == 0) {
                return $this->redirectWithFlash('projects_edit_roles', 'Nalezy wybrać lidera projektu', 'error', array(
                            'projekt_nazwa' => $projekt->getName(),
                ));
            }
            $m->flush();
            return $this->redirectWithFlash('projects_edit_roles', 'Zmieniono role w projekcie', 'success', array(
                        'projekt_nazwa' => $projekt->getName(),
            ));
        }



        return $this->render('AppFrontendBundle:Projects:editRoles.html.twig', array(
                    'projekt' => $projekt,
                    'uzytkownicy' => $uzytkownicy,
                    'role' => $role
        ));
    }

    public function addUserToProjectAction() {
        return $this->render('AppFrontendBundle:Projects:addUserToProject.html.twig', array(
        ));
    }

    public function isLider($projekt, $uzytkownik = null) {
        $uzytkownik = $uzytkownik != null ? $uzytkownik : $this->getUser();
        return $this->getDoctrine()->getManager()->getRepository('DataDatabaseBundle:UzytkownikProjekt')->findByProjektAndUzytkownik($projekt, $uzytkownik)->getRola();
    }

    public function editProjectAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie istnieje taki projekt', 'error');
        }



        $form = $this->createFormBuilder($projekt)
                ->add('label', null, array('label' => 'Zmień nazwę', 'attr' => array('class' => 'form-control')))
                ->add('name', null, array('label' => 'Url projektu', 'attr' => array('class' => 'form-control')))
                ->add('status', 'choice', array(
                    'label' => 'Status',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                    ),
                    'choices' => Projekt::GetStatusy(),
                    'error_mapping' => 'jazda',
                    'invalid_message' => 'jazda',
                    'required' => false))
                ->add('save', 'submit', array('label' => 'Zapisz', 'attr' => array('class' => 'btn btn-success')))
                ->getForm()
        ;
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($projekt);
                $em->flush();
                
                return $this->redirect($this->generateUrl('projects'));
            }
            
        }
        return $this->render('AppFrontendBundle:Projects:editProject.html.twig', array(
                    'form' => $form->createView(),
        ));
    }
    
     public function deleteProjectAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($projekt_nazwa);

        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie istnieje taki projekt', 'error');
        }
        $id =  $projekt->getId();
        echo $id;
        
        $m->getRepository('DataDatabaseBundle:Projekt')->addToDeleteProjekt($projekt);
        

        return $this->redirect($this->generateUrl('projects'));
     }

}
