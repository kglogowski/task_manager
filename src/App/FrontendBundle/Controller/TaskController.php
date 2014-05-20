<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\Projekt;
use Data\DatabaseBundle\Entity\Task;

class TaskController extends TmController {

    public function indexAction($projekt_nazwa, $task_id) {
        $task = null;
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
            if ($task->getStatus() == Task::STATUS_ZAMKNIETY) {
                return $this->redirectWithFlash('projects', 'Zadanie jest zamknięte', 'info');
            }
            $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getCreator());
            $aktualny = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($task->getAktualnyUzytkownik());
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
                        'choices' => array('_vns_' => 'Aktualny: ' . $aktualny->getLogin() . ' ') + $task->getUzytkownicyToDropdown(),
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
                                $plikWiadomosci->move($plik);
                            } else {
                                $errorString .= ', plik ' . $plik->getClientOriginalName() . ' jest za duży lub niepoprawny';
                            }
                        }
                    }
                    $task
                            ->setAktualnyUzytkownik($aktualnyId)
                            ->setStatus($statusId);
                    $m->persist($task);
                    $m->flush();
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
            ));
        } else {
            $creator = $m->getRepository('DataDatabaseBundle:Uzytkownik')->find($projekt->getCreator());
        }

        return $this->render('AppFrontendBundle:Task:index.html.twig', array(
                    'projekt' => $projekt,
                    'task' => $task,
                    'creator' => $creator,
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
                            $plikTask->move($plik);
                        } else {
                            $errorString .= ', plik ' . $plik->getClientOriginalName() . ' jest za duży lub niepoprawny';
                        }
                    }
                }

                $m->flush();
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

}
