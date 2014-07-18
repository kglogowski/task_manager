<?php

namespace App\FrontendBundle\Controller;

use App\LibBundle\TmController;
use Data\DatabaseBundle\Entity\RkOperacja;

class RachunekController extends TmController {

    public function indexAction() {
        $m = $this->getDoctrine()->getManager();
        $kwotaVirtual = $m->getRepository('DataDatabaseBundle:RkOperacja')->getAllKwota();
        $kwotaReal = $m->getRepository('DataDatabaseBundle:RkOperacja')->getAllKwota(TRUE);
        return $this->render('AppFrontendBundle:Rachunek:index.html.twig', array(
            'virtual' => $kwotaVirtual[1],
            'real' => $kwotaReal[1],
        ));
    }

    public function newAction() {
        $m = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                ->add('label', 'text', array(
                    'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Nazwa transakcji',
                        'title' => 'Podaj nazwę transakcji',
                    )
                ))
                ->add('price', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Kwota netto',
                        'class' => 'form-control'
                    ))
                )
                ->add('zatwierdzone', 'checkbox', array(
                    'label' => 'Zatwierdzone',
                    'required' => false,
                        )
                )
                ->add('save', 'submit', array(
                    'label' => 'Stwórz transakcje',
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
                $kwota = \App\LibBundle\Float::toFloat($data['price']);
                if ($kwota == FALSE) {
                    $this->get('session')->getFlashBag()->set('error', 'Podana kwota jest nieprawidłowa');
                    return $this->redirect($this->generateUrl('rachunek_new'));
                }
                $objTransakcja = new RkOperacja();
                $objTransakcja
                        ->setLabel($data['label'])
                        ->setKwotaNetto($kwota)
                        ->setConfirm($data['zatwierdzone']);
                $m->persist($objTransakcja);
                $m->flush();
                $this->get('session')->getFlashBag()->set('success', 'Stworzono nową transakcje');
                return $this->redirect($this->generateUrl('rachunek'));
            }
        }
        return $this->render('AppFrontendBundle:Rachunek:new.html.twig', array(
                    'form' => $form->createView()
        ));
    }

}
