<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\UzytkownikProjekt;
use Symfony\Component\HttpFoundation\Request;

class ProjectsController extends TmController {

    public function indexAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $collMyProjekt = $projektRepo->getProjectByUser($uzytkownik);
        $restProject = $projektRepo->getRestOfProject($uzytkownik);
        return $this->render('AppFrontendBundle:Projects:index.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'restProjects' => $restProject,
                    'UzytkownikProjekt' => new UzytkownikProjekt(),
                    'Projekt' => new Projekt()
        ));
    }

    public function zakonczoneAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $collMyProjekt = $projektRepo->getProjectByUserZakonczone($uzytkownik);
        $restProject = $projektRepo->getRestOfProjectZakonczone($uzytkownik);
        return $this->render('AppFrontendBundle:Projects:zakonczone.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'restProjects' => $restProject,
                    'UzytkownikProjekt' => new UzytkownikProjekt(),
                    'Projekt' => new Projekt()
        ));
    }

    public function skasowaneAction() {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $this->getUser();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $collMyProjekt = $projektRepo->getProjectByUserSkasowane($uzytkownik);
        $restProject = $projektRepo->getRestOfProjectSkasowane($uzytkownik);

        return $this->render('AppFrontendBundle:Projects:skasowane.html.twig', array(
                    'myProjects' => $collMyProjekt,
                    'restProjects' => $restProject,
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
            $form = $this->createForm(new \App\FrontendBundle\Lib\Form\AddUserToProjectForm($m, $projekt));
            if ($request->request->has($form->getName())) {
                $form->handleRequest($request);
                $data = $form->getData();
                $users = $data['uzytkownicy'];
                $collUp = $projekt->getUzytkownicyProjekty();
                $arrStarzy = array();
                foreach ($collUp as $up) {
                    $u = $up->getUzytkownik();
                    if (!in_array($u->getId(), $users)) {
                        if ($this->isLider($projekt, $u)) {
                            return $this->redirectWithFlash('projects_edit_roles', 'Nie można usunąć lidera projektu', 'error', array(
                                        'projekt_nazwa' => $projekt->getName(),
                            ));
                        }
                        $m->remove($up);
                    } else {
                        $arrStarzy[] = $u->getId();
                    }
                }
                $arrResult = array_diff($users, $arrStarzy);
                foreach ($arrResult as $uzytkownikId) {
                    $uzytkownik = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($uzytkownikId);
                    $newUp = new UzytkownikProjekt();
                    $newUp
                            ->setProjekt($projekt)
                            ->setUzytkownik($uzytkownik)
                            ->setRola(UzytkownikProjekt::ROLA_POMOCNIK);
                    $m->persist($newUp);
                }
                $m->flush();
                return $this->redirectWithFlash('projects_edit_roles', 'Zaktualizowano użytkowników należących do projektu', 'success', array(
                            'projekt_nazwa' => $projekt->getName(),
                ));
            } else {
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
        }



        return $this->render('AppFrontendBundle:Projects:editRoles.html.twig', array(
                    'projekt' => $projekt,
                    'uzytkownicy' => $uzytkownicy,
                    'role' => $role
        ));
    }

    public function addUserToProjectAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($request->get('projekt_name'));
        $form = $this->createForm(new \App\FrontendBundle\Lib\Form\AddUserToProjectForm($m, $projekt));
        return $this->render('AppFrontendBundle:Projects:addUserToProject.html.twig', array(
                    'form' => $form->createView(),
        ));
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
                    'required' => false))
                ->add('termin', 'date', array(
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'attr' => array(
                        'class' => 'form-control date_to',
                        'placeholder' => 'Podaj termin'
                    ))
                )
                ->add('save', 'submit', array('label' => 'Zapisz', 'attr' => array('class' => 'btn btn-success')))
                ->getForm()
        ;
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {
              
                $em = $this->getDoctrine()->getManager();
                $em->persist($projekt);
                $em->flush();

                 return $this->redirectWithFlash('projects', 'projekt został zaktualizowany', 'success');
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
        $m->getRepository('DataDatabaseBundle:Projekt')->addToDeleteProjekt($projekt);


        return $this->redirect($this->generateUrl('projects'));
    }

    public function restoreProjectAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($projekt_nazwa);

        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie istnieje taki projekt', 'error');
        }

        $m->getRepository('DataDatabaseBundle:Projekt')->removeFromDeleteProjekt($projekt);


        return $this->redirect($this->generateUrl('projects_skasowane'));
    }

    public function deleteHardProjectAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projektRepo = $m->getRepository('DataDatabaseBundle:Projekt');
        $projekt = $projektRepo->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie istnieje taki projekt', 'error');
        }
        $tasks = $projekt->getTasks();
        foreach ($tasks as $task) {
            $messages = $task->getWiadomosci();
            $id = $task->getId();
            foreach ($messages as $message) {
                $plikiWiadomosci = $message->getPlikiWiadomosci();
                foreach ($plikiWiadomosci as $plikWiadomosci) {
                    $m->remove($plikWiadomosci);
                }
                $m->remove($message);
            }
            foreach ($task->getPlikiTask() as $plikTask) {
                $m->remove($plikTask);
            }
            $m->remove($task);
        }
        foreach ($projekt->getUzytkownicyProjekty() as $up) {
            $m->remove($up);
        }
        $m->remove($projekt);
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_wiadomosci/' . $projekt->getId())) {
            $this->deleteDir($_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_wiadomosci/' . $projekt->getId());
        }
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_task/' . $projekt->getId())) {
            $this->deleteDir($_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_task/' . $projekt->getId());
        }
        $m->flush();

        return $this->redirect($this->generateUrl('projects_skasowane'));
    }

}
