<?php

namespace App\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Data\DatabaseBundle\Entity\Uzytkownik;
use App\LibBundle\Uzytkownik\UzytkownikFilter;

class UzytkownikController extends Controller
{

    public function newAction() {
        $m = $this->getDoctrine()->getManager();
        $object = new Uzytkownik();
        	$form = $this->createFormBuilder($object)
		->add('imie', 'text', array('attr' => array ( 'class' =>  'form-control' ) ))
		->add('nazwisko', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('login', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('email', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('haslo', 'password', array('attr' => array ( 'class' =>  'form-control' )))
		->add('Zapisz', 'submit', array(
                    'attr' => array(
                        'class' =>  'btn btn-sm btn-success'
                    )
                ))
			->getForm();

        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($object);
                $object
                        ->setHaslo($form['haslo']->getData(), $factory)
                        ->setIsActive(TRUE)
                        ->addRole($m->getRepository('DataDatabaseBundle:Role')->findOneByNazwa('ROLE_USER'));
                $m->persist($object);
                $m->flush();
                return $this->redirect($this->generateUrl('uzytkownik_show'));
            }
        }
        return $this->render('AdminBundle:Uzytkownik:new.html.twig', array('form' => $form->createView()));
    }

    public function showAction($page) {
        $m = $this->getDoctrine()->getManager();
        $offset = ($page-1)*10;
        $limit = 10;
        return $this->render('AdminBundle:Uzytkownik:show.html.twig', array('count' => count($m->getRepository('DataDatabaseBundle:Uzytkownik')->findAll()) ,'page' => $page, 'collection' => $m->getRepository('DataDatabaseBundle:Uzytkownik')->findBy(array(),array('id' => 'ASC'), $limit, $offset)));
    }
    
    public function deleteAction($id) {
        $m = $this->getDoctrine()->getManager();
        $object = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneById($id);
        $m->remove($object);
        $m->flush();
        return $this->redirect($this->generateUrl('uzytkownik_show'));
    }

    public function editAction($id) {
        $m = $this->getDoctrine()->getManager();
        $object = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneById($id);
        	$form = $this->createFormBuilder($object)
		->add('imie', 'text', array('attr' => array ( 'class' =>  'form-control' ) ))
		->add('nazwisko', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('login', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('email', 'text', array('attr' => array ( 'class' =>  'form-control' )))
		->add('Zapisz', 'submit', array(
                    'attr' => array(
                        'class' =>  'btn btn-sm btn-success'
                    )
                ))
			->getForm();

        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $m->persist($object);
                $m->flush();
                return $this->redirect($this->generateUrl('uzytkownik_show'));
            }
        }
        return $this->render('AdminBundle:Uzytkownik:edit.html.twig', array('form' => $form->createView()));
    }
    
    public function configAction($id) {
        $m = $this->getDoctrine()->getManager();
        $uzytkownik = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneById($id);
        $roles = $uzytkownik->getRoles();
        $roles_all = $m->getRepository('DataDatabaseBundle:Role')->findAll();
        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            foreach ($_POST as $key => $on) {
                if(!in_array($key, $roles)) {
                    $uzytkownik->addRole($m->getRepository('DataDatabaseBundle:Role')->findOneByNazwa($key));
                }
            }
            foreach ($roles as $role) {
                if(!in_array($role, array_keys($_POST))) {
                    $uzytkownik->removeRole($m->getRepository('DataDatabaseBundle:Role')->findOneByNazwa($role));
                }
            }
            $m->persist($uzytkownik);
            $m->flush();
            $this->get('session')->getFlashbag()->add('success','Zmieniono role użytkownikowi');
            return $this->redirect($this->generateUrl('uzytkownik_config', array('id'  =>  $uzytkownik->getId())));
        }
        return $this->render('AdminBundle:Uzytkownik:config.html.twig', array(
            'roles' =>  $roles,
            'roles_all' =>  $roles_all,
            'uzytkownik'    =>  $uzytkownik
        ));
    }
    
}
