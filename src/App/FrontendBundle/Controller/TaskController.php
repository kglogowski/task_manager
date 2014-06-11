<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\Task;
use Data\DatabaseBundle\Entity\Wiadomosc;
use Symfony\Component\HttpFoundation\Request;
use Data\DatabaseBundle\Entity\PlikWiadomosci;

class TaskController extends TmController {

    public function indexAction($projekt_nazwa, $task_id) {
        $task = null;
        $archiwalne = $this->getRequest()->get('archiwalne');
        $m = $this->getDoctrine()->getManager();
        $projekt = $m->getRepository('DataDatabaseBundle:Projekt')->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie ma tekigo projektu', 'error');
        }

        if ($task_id != 0) {
            $task = $m->getRepository('DataDatabaseBundle:Task')->find($task_id);
            if (!$task instanceof Task) {
                return $this->redirectWithFlash('projects', 'Nie ma tekigo zadania', 'error');
            }
            if ($task->getProjekt()->getId() != $projekt->getId()) {
                return $this->redirectWithFlash('projects', 'Zadanie jest przypisane do innego projektu', 'error');
            }
//            if ($task->getStatus() == Task::STATUS_ZAMKNIETY) {
//                return $this->redirectWithFlash('projects', 'Zadanie jest zamknięte', 'info');
//            }
            $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
            $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());
            $arrUzytkownicyToDropDown = array('_vns_' => 'Aktualny: ' . $aktualny->getLogin() . ' ') + $task->getUzytkownicyToDropdown();
            $form = $this->createFormBuilder()
                    ->add('tekst', 'textarea', array(
                        'attr' => array(
                            'class' => 'tinymce',
                            'data-theme' => 'bbcode',
                            'placeholder' => 'Napisz wiadomość',
                            'title' => 'Napisz wiadomość',
                        )
                    ))
                    ->add('aktualny', 'choice', array(
                        'choices' => $arrUzytkownicyToDropDown,
                        'required' => 'true',
                        'attr' => array(
                            'class' => 'form-control selectpicker',
                            'data-style' => 'btn-default',
                            'title' => 'Przypnij zadanie na:',
                        )
                    ))
                    ->add('status', 'choice', array(
                        'choices' => array('_vns_' => 'Aktualny status: ' . $task->getStatusLabel() . ' ') + Task::GetStatusyForDropDown(),
                        'required' => 'true',
                        'attr' => array(
                            'class' => 'form-control selectpicker',
                            'data-style' => 'btn-default',
                            'title' => 'Ustaw status',
                        )
                    ))
                    ->add('pliki', 'file', array(
                        'required' => false,
                        'attr' => array(
                            'multiple' => 'multiple',
                            'id' => 'files'
                        )
                    ))
                    ->add('save', 'submit', array(
                        'label' => 'Zapisz wiadomość',
                        'attr' => array(
                            'class' => 'btn btn-success'
                        )
                    ))
                    ->getForm();
            $request = $this->getRequest();
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    if ($data['tekst'] === NULL) {
                        return $this->redirectWithFlash('tasks', 'Treść wiadomości nie może być pusta', 'error', array(
                                    'projekt_nazwa' => $projekt->getName(),
                                    'task_id' => $task->getId()
                        ));
                    }
                    $aktualnyId = $data['aktualny'] == '_vns_' ? $task->getAktualnyUzytkownik() : $data['aktualny'];
                    $statusId = $data['status'] == '_vns_' ? $task->getStatus() : $data['status'];
                    $wiadomosc = new \Data\DatabaseBundle\Entity\Wiadomosc();
                    $wiadomosc
                            ->setCreatedAt()
                            ->setTask($task)
                            ->setTresc($data['tekst'])
                            ->setUzytkownik($this->getUser())
                            ->setNumer(count($task->getWiadomosci()) + 1);
                    ;
                    $m->persist($wiadomosc);
                    $errorString = '';
                    foreach ($data['pliki'] as $plik) {
                        if ($plik instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                            if ($plik->isValid()) {
                                $plikWiadomosci = new \Data\DatabaseBundle\Entity\PlikWiadomosci();
                                $plikWiadomosci
                                        ->setLabel($plik->getClientOriginalName())
                                        ->setWiadomosc($wiadomosc)
                                        ->setTyp($plik->getMimeType())
                                ;
                                $m->persist($plikWiadomosci);
                                $m->flush();
                                $plikWiadomosci->move($plik);
                            } else {
                                $errorString .= ', plik ' . $plik->getClientOriginalName() . ' jest za duży lub niepoprawny';
                            }
                        }
                    }
                    $task
                            ->setAktualnyUzytkownik($aktualnyId)
                            ->setStatus($statusId);
                    if ($this->getUser()->getId() != $aktualnyId) {
                        $task->setPoprzedniUzytkownik($this->getUser()->getId());
                    }
                    $m->persist($task);

                    $m->flush();
                    if ($this->getUser()->getId() != $aktualnyId) {
                        $this->sendMailInfo(
                                array($m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik())), $this->getUser()->getLogin() . " przepiął na Ciebie zadanie: " . $task->getLabel(), $this->renderView('AppFrontendBundle:Common:mailSwitchUserTask.html.twig', array(
                                    'wiadomosc' => $wiadomosc,
                                    'aktualny' => $this->getUser()->getLogin(),
                                    'task' => $task,
                                ))
                        );
                    }
                    if ($statusId == Task::STATUS_ZAMKNIETY) {
                        $this->sendMailInfo(
                                $task->getUzytkownicy(), "Zadanie: " . $task->getLabel() . ' zostało zamknięte', $this->renderView('AppFrontendBundle:Common:mailTaskClosed.html.twig', array(
                                    'aktualny' => $this->getUser()->getLogin(),
                                    'task' => $task,
                                ))
                        );
                    }
                    return $this->redirectWithFlash('tasks', 'Wiadmość została dodana' . $errorString, 'success', array(
                                'projekt_nazwa' => $projekt->getName(),
                                'task_id' => $task->getId()
                    ));
                }
            }
            return $this->render('AppFrontendBundle:Task:index.html.twig', array(
                        'projekt' => $projekt,
                        'task' => $task,
                        'creator' => $creator,
                        'form' => $form->createView(),
                        'aktualny' => $aktualny,
                        'archiwalne' => $archiwalne,
            ));
        } else {
            $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($projekt->getCreator());
        }

        return $this->render('AppFrontendBundle:Task:index.html.twig', array(
                    'projekt' => $projekt,
                    'task' => $task,
                    'creator' => $creator,
                    'archiwalne' => $archiwalne,
        ));
    }

    public function getNewFormMessageAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $task = $m->getRepository('DataDatabaseBundle:Task')->find($request->get('task_id'));
        $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
        $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());
        $arrUzytkownicyToDropDown = array('_vns_' => 'Aktualny: ' . $aktualny->getLogin() . ' ') + $task->getUzytkownicyToDropdown();
        $form = $this->createFormBuilder()
                ->add('tekst', 'textarea', array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'bbcode',
                        'placeholder' => 'Napisz wiadomość',
                        'title' => 'Napisz wiadomość',
                    )
                ))
                ->add('aktualny', 'choice', array(
                    'choices' => $arrUzytkownicyToDropDown,
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Przypnij zadanie na:',
                    )
                ))
                ->add('status', 'choice', array(
                    'choices' => array('_vns_' => 'Aktualny status: ' . $task->getStatusLabel() . ' ') + Task::GetStatusyForDropDown(),
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Ustaw status',
                    )
                ))
                ->add('pliki', 'file', array(
                    'required' => false,
                    'attr' => array(
                        'multiple' => 'multiple',
                        'id' => 'files'
                    )
                ))
                ->add('save', 'submit', array(
                    'label' => 'Zapisz wiadomość',
                    'attr' => array(
                        'class' => 'btn btn-success'
                    )
                ))
                ->getForm();
        return $this->render('AppFrontendBundle:Task:getNewFormMessage.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function newAction($projekt_nazwa) {
        $m = $this->getDoctrine()->getManager();
        $projekt = $m->getRepository('DataDatabaseBundle:Projekt')->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie ma tekigo projektu', 'error');
        }

        $uzytkownicy = array();
        $collUp = $projekt->getUzytkownicyProjekty();
        foreach ($collUp as $up) {
            $user = $up->getUzytkownik();
            $uzytkownicy[$user->getId()] = $user->getLogin();
        }



        $form = $this
                ->createFormBuilder()
                ->add('label', 'text', array(
                    'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Nazwa zadania',
                        'title' => 'Podaj nazwę zadania',
                    )
                ))
                ->add('uzytkownicy', 'choice', array(
                    'multiple' => true,
                    'choices' => $uzytkownicy,
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Wybierz osoby które będą wykonywać to zadanie',
                    )
                ))
                ->add('aktualny', 'choice', array(
                    'choices' => array('_vns_' => 'Przepnij zadanie na:') + $uzytkownicy,
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Przypnij zadanie na:',
                    )
                ))
                ->add('priorytet', 'choice', array(
                    'choices' => array('_vns_' => 'Ustal priorytet') + Task::GetProtytety(),
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Ustal priorytet',
                    )
                ))
                ->add('date_to', 'date', array(
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'attr' => array(
                        'class' => 'form-control date_to',
                        'placeholder' => 'Podaj termin'
                    ))
                )
                ->add('opis', 'textarea', array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'placeholder' => 'Napisz co należy wykonać w zadaniu',
                        'title' => 'Napisz wiadomość',
                    )
                ))
                ->add('save', 'submit', array(
                    'label' => 'Stwórz zadanie',
                    'attr' => array(
                        'class' => 'btn btn-success'
                    )
                ))
                ->add('pliki', 'file', array(
                    'required' => false,
                    'attr' => array(
                        'multiple' => 'multiple',
                        'id' => 'files'
                    )
                ))
                ->getForm();



        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['aktualny'] == '_vns_') {
                    return $this->redirectWithFlash('task_new', 'Przypnij zadanie na któregoś użytkownika z listy', 'error', array('projekt_nazwa' => $projekt->getName()));
                }
                if ($data['priorytet'] == '_vns_') {
                    return $this->redirectWithFlash('task_new', 'Ustal priorytet zadania', 'error', array('projekt_nazwa' => $projekt->getName()));
                }
                $task = new Task();
                foreach ($data['uzytkownicy'] as $userId) {
                    $task->addUzytkownik($m->find('DataDatabaseBundle:Uzytkownik', $userId));
                }
                $task
                        ->setAktualnyUzytkownik($data['aktualny'])
                        ->setPriorytet($data['priorytet'])
                        ->setTermin($data['date_to'])
                        ->setLabel($data['label'])
                        ->setOpis($data['opis'])
                        ->setProjekt($projekt)
                        ->setCreatedAt()
                        ->setUpdatedAt()
                        ->setStatus(Task::STATUS_NOWY)
                        ->setCreator($this->getUser()->getId())
                ;
                $m->persist($task);
                foreach ($data['pliki'] as $plik) {
                    if ($plik instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                        if ($plik->isValid()) {
                            $plikTask = new \Data\DatabaseBundle\Entity\PlikTask();
                            $plikTask
                                    ->setLabel($plik->getClientOriginalName())
                                    ->setTask($task)
                                    ->setTyp($plik->getMimeType())
                            ;
                            $m->persist($plikTask);
                            $m->flush();
                            $plikTask->move($plik);
                        } else {
                            $errorString .= ', plik ' . $plik->getClientOriginalName() . ' jest za duży lub niepoprawny';
                        }
                    }
                }
                $m->flush();
                $this->sendMailInfo(
                        $task->getUzytkownicy(), 'Zostało stworzone zadanie o nazwie: ' . $task->getLabel(), $this->renderView('AppFrontendBundle:Common:mailCreateTask.html.twig', array(
                            'task' => $task,
                            'aktualny' => $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik()),
                        ))
                );
                return $this->redirectWithFlash('tasks', 'Stworzono nowe zadanie', 'success', array('projekt_nazwa' => $projekt->getName()));
            }
        }
        return $this->render('AppFrontendBundle:Task:new.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function plikWiadomoscPobierzAction($plikWiadomosciId) {
        $m = $this->getDoctrine()->getManager();
        $plikWiadomosci = $m->find("DataDatabaseBundle:PlikWiadomosci", $plikWiadomosciId);
        $wiadomosc = $plikWiadomosci->getWiadomosc();
        $task = $wiadomosc->getTask();
        $projektId = $task->getProjekt()->getId();
        $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_wiadomosci/' . $projektId . '/' . $task->getId() . '/' . $wiadomosc->getId() . '/' . $plikWiadomosciId;

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$path");
        header("Content-Disposition: inline; filename=" . $plikWiadomosci->getLabel());
        header("Content-Type: " . $plikWiadomosci->getTyp());
        header("Content-Transfer-Encoding: binary");

        // Read the file from disk
        readfile($path);
        die;
    }

    public function plikTaskPobierzAction($plikTaskId) {
        $m = $this->getDoctrine()->getManager();
        $plikTask = $m->find("DataDatabaseBundle:PlikTask", $plikTaskId);
        $task = $plikTask->getTask();
        $projektId = $task->getProjekt()->getId();
        $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/pliki_task/' . $projektId . '/' . $task->getId() . '/' . $plikTaskId;
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$path");
        header("Content-Disposition: inline; filename=" . $plikTask->getLabel());
        header("Content-Type: " . $plikTask->getTyp());
        header("Content-Transfer-Encoding: binary");
        // Read the file from disk
        readfile($path);
        die;
    }

    public function taskReopenAction($task_id) {
        $m = $this->getDoctrine()->getManager();
        $task = $m->getRepository('DataDatabaseBundle:Task')->find($task_id);
        if (!$task instanceof Task) {
            return $this->redirectWithFlash('projects', 'Podane zadanie nie istnieje', 'error');
        }
        if (!$task->isZakonczony()) {
            return $this->redirectWithFlash('projects', 'Zadanie nie jest zamknięte', 'error');
        }

        $projekt = $task->getProjekt();
        if ($projekt->isZakonczony()) {
            return $this->redirectWithFlash('projects', 'Nie można otworzyć zadania, ponieważ projekt jest zamknięty', 'error');
        }

        if (!$this->isLider($projekt)) {
            return $this->redirectWithFlash('projects', 'Nie posiadasz wystarczających praw aby otworzyć projekt, skontaktuj się z liderem projektu', 'error');
        }

        $task->setStatus(Task::STATUS_PRZYWROCONY);
        $m->persist($task);

        $m->flush();
        $this->sendMailInfo(
                $task->getUzytkownicy(), "Zadanie: " . $task->getLabel() . ' zostało na nowo otwarte', $this->renderView('AppFrontendBundle:Common:mailTaskReopened.html.twig', array(
                    'aktualny' => $this->getUser()->getLogin(),
                    'task' => $task,
                ))
        );
        return $this->redirectWithFlash('tasks', 'Zadanie zostało przywrócone', 'success', array(
                    'projekt_nazwa' => $projekt->getName(),
                    'task_id' => $task->getId()
                        )
        );
    }

    public function usunWiadomoscAction($wiadomoscId) {
        $m = $this->getDoctrine()->getManager();
        $wiadomosc = $m->getRepository('DataDatabaseBundle:Wiadomosc')->find($wiadomoscId);
        $task = $wiadomosc->getTask();
        $projekt = $task->getProjekt();
        if ($wiadomosc->getUzytkownik() !== $this->getUser()) {
            return $this->redirectWithFlash('tasks', 'Nie jesteś właścicelem tej wiadomości', 'error', array(
                        'projekt_nazwa' => $projekt->getName(),
                        'task_id' => $task->getId()
            ));
        }
        if (!$wiadomosc->canBeDelete()) {
            return $this->redirectWithFlash('tasks', 'Można usunąć jedynie najnowszą wiadomość w zadaniu', 'error', array(
                        'projekt_nazwa' => $projekt->getName(),
                        'task_id' => $task->getId()
            ));
        }
        $wiadomosc->delete($m);
        return $this->redirectWithFlash('tasks', 'Wiadomość została usunięta', 'success', array(
                    'projekt_nazwa' => $projekt->getName(),
                    'task_id' => $task->getId()
        ));
    }

    public function edytujWiadomoscAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $objWiadomosc = $m->getRepository('DataDatabaseBundle:Wiadomosc')->find((int) $request->get('wiadomosc_id'));
        if (!$objWiadomosc instanceof Wiadomosc) {
            
        }
        $form = $this->createForm(new \App\FrontendBundle\Lib\Form\WiadomoscEditForm($m, $objWiadomosc));

        return $this->render('AppFrontendBundle:Task:edytujWiadomosc.html.twig', array(
                    'form' => $form->createView(),
                    'wiadomosc' => $objWiadomosc,
        ));
    }

    public function edytujWiadomoscFormValidAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $wiadomosc = $m->getRepository('DataDatabaseBundle:Wiadomosc')->find((int) $request->get('wiadomosc_id'));
        $task = $wiadomosc->getTask();
        $projekt = $task->getProjekt();
        $editForm = $this->createForm(new \App\FrontendBundle\Lib\Form\WiadomoscEditForm($m, $wiadomosc));
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $data = $editForm->getData();
            if ($editForm->isValid()) {
                $aktualnyId = $data['aktualny'] == '_vns_' ? $task->getAktualnyUzytkownik() : $data['aktualny'];
                $statusId = $data['status'] == '_vns_' ? $task->getStatus() : $data['status'];
                $wiadomosc
                        ->setUpdatedAt(new \DateTime('now'))
                        ->setTresc($data['tekst'])
                        ->setUzytkownik($this->getUser());
                $task
                        ->setAktualnyUzytkownik($aktualnyId)
                        ->setStatus($statusId);
                if ($this->getUser()->getId() != $aktualnyId) {
                    $task->setPoprzedniUzytkownik($this->getUser()->getId());
                }
                $m->persist($task);
                $m->persist($wiadomosc);
                $m->flush();
                return $this->redirectWithFlash('tasks', 'Wiadmość została zaktualizowana', 'success', array(
                            'projekt_nazwa' => $projekt->getName(),
                            'task_id' => $task->getId()
                ));
            }
        }
    }

    public function fileDeleteFromMessageAction(Request $request) {
        $m = $this->getDoctrine()->getManager();
        $plik = $m->getRepository("DataDatabaseBundle:PlikWiadomosci")->find($request->get('plik_id'));
        if (!$plik instanceof PlikWiadomosci) {
            
        }
        $plik->delete($m);
        return new \Symfony\Component\HttpFoundation\Response();
    }

    public function taskEditAction($projekt_nazwa, $task_id) {

        $m = $this->getDoctrine()->getManager();
        $task = $m->getRepository('DataDatabaseBundle:Task')->findOneById($task_id);

        $projekt = $m->getRepository('DataDatabaseBundle:Projekt')->findOneByName($projekt_nazwa);
        if (!$projekt instanceof Projekt) {
            return $this->redirectWithFlash('projects', 'Nie ma tekigo projektu', 'error');
        }

        $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
        $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());

        $aktualniUzytkownicyZadania = array();
        foreach ($task->getUzytkownicy() as $user) {
            $aktualniUzytkownicyZadania[$user->getId()] = $user->getLogin();
        }
        $arrChoice = array_keys($aktualniUzytkownicyZadania);

        $uzytkownicy = array();
        $collUp = $projekt->getUzytkownicyProjekty();
        foreach ($collUp as $up) {
            $user = $up->getUzytkownik();
            $uzytkownicy[$user->getId()] = $user->getLogin();
        }



        $form = $this->createFormBuilder()
                ->add('uzytkownicy', 'choice', array(
                    'multiple' => true,
                    'choices' => $uzytkownicy,
                    'preferred_choices' => $arrChoice,
                    'data' => $arrChoice,
                    'label' => 'Użytkownicy przypisani do zadania',
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Wybierz osoby które będą wykonywać to zadanie'
                    ))
                )
                ->add('AktualnyUzytkownik', 'choice', array(
                    'label' => 'Aktualnie przypisany użytkownik',
                    'choices' => $aktualniUzytkownicyZadania,
                    'preferred_choices' => array($aktualny->getId()),
                    'data' => $aktualny->getId(),
                    'required' => 'true',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Przypnij zadanie na:',
                    )
                ))
                ->add('priorytet', 'choice', array(
                    'label' => 'Priorytet',
                    'choices' => Task::GetProtytety(),
                    'required' => 'true',
                    'preferred_choices' => array($task->getPriorytet()),
                    'data' => $task->getPriorytet(),
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'data-style' => 'btn-default',
                        'title' => 'Ustal priorytet',
                    )
                ))
                ->add('termin', 'date', array(
                    'label' => 'Termin ukończenia zadania',
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'data' => $task->getTermin(),
                    'attr' => array(
                        'class' => 'form-control date_to',
                        'placeholder' => 'Podaj termin'
                    ))
                )
                ->add('opis', 'textarea', array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'placeholder' => 'Napisz co należy wykonać w zadaniu',
                        'data' => $task->getOpis(),
                        'title' => 'Napisz wiadomość',
                    )
                ))
                ->add('save', 'submit', array(
                    'label' => 'aktualizuj zadanie',
                    'attr' => array(
                        'class' => 'btn btn-success'
                    )
                ))
                ->getForm();

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $data = $form->getData();
                $users = $data['uzytkownicy'];
                $collUt = $task->getUzytkownicy();
                $arrStarzy = array();
                $arrUsersUnsetMail = array();
                $arrUsersAddMail = array();
                foreach ($collUt as $ut) {
                    if (!in_array($ut->getId(), $users)) {
                        $task->removeUzytkownik($ut);
                        $arrUsersUnsetMail[] = $ut;
                    } else {
                        $arrStarzy[] = $ut->getId();
                    }
                }
                $arrResult = array_diff($users, $arrStarzy);
                foreach ($arrResult as $uzytkownikId) {
                    $uzytkownik = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($uzytkownikId);
                    $arrUsersAddMail[] = $uzytkownik;
                    $task->addUzytkownik($uzytkownik);
                }
                $task
                        ->setAktualnyUzytkownik($data['AktualnyUzytkownik'])
                        ->setPriorytet($data['priorytet'])
                        ->setTermin($data['termin'])
                        ->setOpis($data['opis'])
                        ->setUpdatedAt();
                $m->persist($task);

                $this->sendMailInfo($arrUsersAddMail, "Zostałeś dodany do zadania: " . $projekt->getLabel(), $this->renderView("AppFrontendBundle:Common:mailChangeUserToTask.html.twig", array(
                            'task' => $task,
                            'add' => true,
                )));
                $this->sendMailInfo($arrUsersUnsetMail, "Zostałeś usunięty z zadania: " . $projekt->getLabel(), $this->renderView("AppFrontendBundle:Common:mailChangeUserToTask.html.twig", array(
                            'task' => $task,
                            'add' => false,
                )));

                $m->flush();
//                $m->persist($task);
//                $m->flush();
                return $this->redirectWithFlash('tasks', 'Zaktualizowano zadanie', 'success', array('projekt_nazwa' =>
                            $projekt->getName(),
                            'task_id' => $task_id));
            }
        }

        return $this->render('AppFrontendBundle:Task:editTask.html.twig', array(
                    'form' => $form->createView(),
                    'projekt' => $projekt,
                    'task' => $task
        ));
    }

}
