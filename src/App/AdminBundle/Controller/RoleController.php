<?php

namespace App\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Data\DatabaseBundle\Entity\Role;

class RoleController extends Controller
{

    public function newAction() {
        $m = $this->getDoctrine()->getManager();
        $object = new Role();
        	$form = $this->createFormBuilder($object)
		->add('nazwa', 'text', array('attr' => array ( 'class' =>  'form-control' ) ))
		->add('Zapisz', 'submit', array('attr' => array('class' => 'btn btn-success btn-sm')))
			->getForm();

        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $m->persist($object);
                $m->flush();
                return $this->redirect($this->generateUrl('role_show'));
            }
        }
        return $this->render('AdminBundle:Role:new.html.twig', array('form' => $form->createView()));
    }

    public function showAction($page) {
        $m = $this->getDoctrine()->getManager();
        $offset = ($page-1)*10;
        $limit = 10;
        return $this->render('AdminBundle:Role:show.html.twig', array('count' => count($m->getRepository('DataDatabaseBundle:Role')->findAll()) ,'page' => $page, 'collection' => $m->getRepository('DataDatabaseBundle:Role')->findBy(array(),array('id' => 'ASC'), $limit, $offset)));
    }
    
    public function deleteAction($id) {
        $m = $this->getDoctrine()->getManager();
        $object = $m->getRepository('DataDatabaseBundle:Role')->findOneById($id);
        $m->remove($object);
        $m->flush();
        return $this->redirect($this->generateUrl('role_show'));
    }

    public function editAction($id) {
        $m = $this->getDoctrine()->getManager();
        $object = $m->getRepository('DataDatabaseBundle:Role')->findOneById($id);
        	$form = $this->createFormBuilder($object)
		->add('nazwa', 'text', array('attr' => array ( 'class' =>  'form-control' ) ))
		->add('Zapisz', 'submit', array('attr' => array('class' => 'btn btn-success btn-sm')))
			->getForm();

        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $m->persist($object);
                $m->flush();
                return $this->redirect($this->generateUrl('role_show'));
            }
        }
        return $this->render('AdminBundle:Role:edit.html.twig', array('form' => $form->createView()));
    }
}
