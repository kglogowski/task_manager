<?php

namespace App\GuardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Data\DatabaseBundle\Entity\Uzytkownik;
use Symfony\Component\Security\Core\SecurityContext;
use App\LibBundle\Guard\RegisterForm;

class DefaultController extends Controller {

    public function loginAction(Request $objRequest) {
        $objSession = $objRequest->getSession();
        if ($objRequest->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $objRequest->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $objSession->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        $objForm = $this->createFormBuilder()
                ->add('login', 'text', array('attr' => array('placeholder' => 'Email lub login:', 'class' => 'form-control')))
                ->add('haslo', 'password', array('attr' => array('placeholder' => 'Hasło:', 'class' => 'form-control')))
                ->add('zaloguj', 'submit', array('label' => 'Zaloguj się', 'attr' => array('class' => 'btn btn-default')))
                ->getForm();

        if ($objRequest->isMethod('POST')) {
            $m = $this->getDoctrine()->getManager();
            $objForm->handleRequest($objRequest);
            $objData = $objForm->getData();
            $session = $this->get('session');
            $query = $m
                    ->createQuery('SELECT u FROM DataDatabaseBundle:Uzytkownik u where u.login = :login OR u.email = :login')
                    ->setParameter(':login', $objData['login']);
            $objUzytkownik = count($query->getResult()) == 1 ? $query->getSingleResult() : null;
            if ($objUzytkownik instanceof Uzytkownik) {
                if ($objUzytkownik->getIsActive() == false) {
                    $this->get('session')->getFlashbag()->set('info', 'Proszę o potwierdzenie konta klikając w link otrzymany w wiadomości email');
                    return $this->redirect($this->generateUrl('_login_path'));
                }
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($objUzytkownik);
                $password = $encoder->encodePassword($objData['haslo'], $objUzytkownik->getSalt());
                if ($objUzytkownik->getHaslo() == $password) {
                    $objUzytkownik->setCountLogin();
                    $objUzytkownik->setLastLogin();
                    $m->persist($objUzytkownik);
                    $m->flush();
                    $token = $objUzytkownik->getAuthenticationToken();
                    $this->get('security.context')->setToken($token);
                    $objSession->set('_security_secured_area', serialize($token));
                    return $this->redirect($this->generateUrl('homepage'));
                } else {
                    $session->getFlashbag()->set('error', 'Zły login lub hasło');
                }
            } else {
                $session->getFlashbag()->set('error', 'Zły login lub hasło');
            }
        }

        return $this->render('AppGuardBundle:Default:login.html.twig', array(
                    'objForm' => $objForm->createView(),
                    'last_username' => $objSession->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    public function registerAction(Request $request) {

        $form = $this->createForm(new RegisterForm());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $m = $this->getDoctrine()->getManager();
                $login = explode('@', $form['email']->getData());
                $objUzytkownik = new Uzytkownik();
                if (count($m->getRepository('DataDatabaseBundle:Uzytkownik')->findByEmail($form['email']->getData())) != 0) {
                    $this->get('session')->getFlashBag()->add('error', 'Istnieje już użytkownik o podanym adresie email');
                    return $this->redirect($this->generateUrl('_register_path'));
                }
                $objUzytkownik->generateToken($m);
                $objUzytkownik
                        ->setHaslo($form['haslo']->getData(), $this->get('security.encoder_factory'))
                        ->setImie($form['imie']->getData())
                        ->setNazwisko($form['nazwisko']->getData())
                        ->setEmail($form['email']->getData())
                        ->setLogin($login[0])
                        ->setUpdatedAt();
                $objUzytkownik->addRole($m->getRepository('DataDatabaseBundle:Role')->findOneByNazwa('ROLE_USER'));
                $m->persist($objUzytkownik);
                $m->flush();
                header("Content-Type:text/html");
                $message = \Swift_Message::newInstance()
                        ->setSubject('Potwierdzenie rejestracji')
                        ->setFrom('send@example.com')
                        ->setTo('k.glogowski2@gmail.com')
                        ->setBody($this->renderView('AppGuardBundle:Default:_register_email.html.twig', array('haslo' => $form['haslo']->getData(), 'user' => $objUzytkownik)))
                        ->setContentType("text/html");
                $this->get('mailer')->send($message);
                $this->get('session')->getFlashbag()->set('success', 'Twoje konto zostało założone. Aby móc dokończyć rejestrację, kliknij w link wysłany w wiadości na podany adres email');
                return $this->redirect($this->generateUrl('_login_path'));
            }
        }

        return $this->render('AppGuardBundle:Default:register.html.twig', array('form' => $form->createView()));
    }

    public function registerConfirmAction($token) {
        $m = $this->getDoctrine()->getManager();
        $user = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneByToken($token);
        if ($user instanceof Uzytkownik) {
            $user->setIsActive(true);
            $m->persist($user);
            $m->flush();
            $this->get('session')->getFlashbag()->set('success', 'Konto zostało potwierdzone, teraz możesz się zalogować');
            return $this->redirect($this->generateUrl('_login_path'));
        } else {
            $this->get('session')->getFlashbag()->set('error', 'Błędny adres');
            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    public function recallPasswordAction() {
        $form = $this->createFormBuilder()
                ->add('email', 'email', array('attr' => array('placeholder' => 'Podaj swój adres email:', 'class' => 'form-control')))
                ->add('wyslij', 'submit', array('label' => "Wyślij powiadomienie", 'attr' => array('class' => 'btn btn-danger')))
                ->getForm();

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $m = $this->getDoctrine()->getManager();
            $user = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneByEmail($form['email']->getData());
            if (!$user instanceof Uzytkownik) {
                $this->get('session')->getFlashbag()->set('error', 'Podany adres email nie istnieje w bazie danych');
                return $this->redirect($this->generateUrl('homepage'));
            }
            $user->generateToken($m);
            $user->setActiveToken(TRUE);
            $m->persist($user);
            $m->flush();
            $message = \Swift_Message::newInstance()
                    ->setSubject('Zapomniałem hasła')
                    ->setFrom(array($this->container->getParameter('mailer_user') => 'Task manager'))
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('AppGuardBundle:Default:_remember_password.html.twig', array('user' => $user)))
                    ->setContentType("text/html");
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashbag()->set('info', 'Wiadomość została wysłana. Przejdź na podany adres email i potwierdź zmianę hasła');
            return $this->redirect($this->generateUrl('_login_path'));
        }
        return $this->render('AppGuardBundle:Default:rememberPassword.html.twig', array('form' => $form->createView()));
    }

    public function forgotPasswordAction($token) {
        $m = $this->getDoctrine()->getManager();
        $user = $m->getRepository('DataDatabaseBundle:Uzytkownik')->findOneByToken($token);
        if (!$user instanceof Uzytkownik) {
            $this->get('session')->getFlashbag()->set('error', 'Link wygasł.');
            return $this->redirect($this->generateUrl('_login_path'));
        }
        $form = $this->createFormBuilder()
                ->add('haslo', 'repeated', array(
                    'invalid_message' => 'Hasła nie zgadzają się',
                    'first_options' => array(
                        'label' => 'Podaj nowe hasło:',
                        'attr' => array(
                            'placeholder' => 'Hasło',
                            'class' => 'form-control',
                        )
                    ),
                    'second_options' => array(
                        'label' => 'Powtórz hasło:',
                        'attr' => array(
                            'class' => 'form-control'
                        )
                    ),
                    'type' => 'password',
                        )
                )
                ->add('zaloguj', 'submit', array('label' => 'Zmień hasło', 'attr' => array('class' => 'btn btn-default')))
                ->getForm();
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setHaslo($form['haslo']->getData(), $this->get('security.encoder_factory'));
                $user->generateToken($m);
                $user->setActiveToken(FALSE);
                $m->persist($user);
                $m->flush();
                $this->get('session')->getFlashbag()->set('success', 'Twoje hasło zostało zmienione');
                return $this->redirect($this->generateUrl('_login_path'));
            }
        }
        return $this->render('AppGuardBundle:Default:forgotPassword.html.twig', array('form' => $form->createView(), 'token' => $token));
    }

}
