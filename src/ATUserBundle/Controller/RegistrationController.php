<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/05/2016
 * Time: 10:26
 */

namespace ATUserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Validator\Constraints\Email;


class RegistrationController extends BaseController
{

    public function registerAction(Request $request)
    {
        $formFactory = $this->get('fos_user.registration.form.factory');
        $userManager = $this->get('at.manager.user_manager');
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid() && $this->get("validator")->validate("email", new Email())) {
                $utilisateur = $userManager->findUserByEmail($form['email']->getData());
                if ($utilisateur != null && $utilisateur->isEnabled()) {
                    // Un utilisateur activé avec l'email utilisée existe déjà en base : on refuse l'inscription
                    $form->get('email')->addError(new FormError($this->get("translator.default")->trans("inscription.erreur.email_utilise")));
                    return $this->render('FOSUserBundle:Registration:register.html.twig', array('form' => $form->createView()));
                }

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                if ($utilisateur != null && !$utilisateur->isEnabled()) {
                    // Un utilisateur désactivé avec l'email utilisée existe déjà en base : on le met à jour
                    $utilisateur->setPlainPassword($user->getPlainPassword());
                    $utilisateur->setEnabled($user->isEnabled()); // L'utilisateur est désactivé si confirmation par email activée (Cf EmailConfirmationListener)
                    $utilisateur->setConfirmationToken($user->getConfirmationToken());
                    $user = $utilisateur;
                }

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}