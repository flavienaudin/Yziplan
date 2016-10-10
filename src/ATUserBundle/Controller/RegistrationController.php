<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/05/2016
 * Time: 10:26
 */

namespace ATUserBundle\Controller;

use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;


class RegistrationController extends BaseController
{

    public function registerAction(Request $request, $display = null)
    {
        $formFactory = $this->get('fos_user.registration.form.factory');
        $userManager = $this->get('at.manager.user');
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $formEvent = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $formEvent);

        if (null !== $formEvent->getResponse()) {
            return $formEvent->getResponse();
        }

        /** @var FormInterface $form */
        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid() && $this->get("validator")->validate("email", new Email())) {
                $email = $form['email']->getData();

                /** @var AccountUser $emailAccountUser */
                $emailAccountUser = $userManager->findUserByEmail($email);
                if ($emailAccountUser != null) {
                    // Un utilisateur avec l'email utilisé existe déjà en base : on refuse l'inscription
                    $form->get('email')->addError(new FormError($this->get("translator.default")->trans("register.validation.email_already_used")));
                    return $this->render('FOSUserBundle:Registration:register.html.twig', array('form' => $form->createView()));
                }

                $formEvent = new FormEvent($form, $request);
                // Update information by listener
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $formEvent);

                $userManager->updateUser($user);

                if (null === $response = $formEvent->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                return $response;
            }
        }

        if ($display == "modal") {
            return $this->render('@ATUser/Registration/partials/modal_registration_form.html.twig', array(
                'form' => $form->createView(),
                'modal' => true
            ));
        } else {
            return $this->render('FOSUserBundle:Registration:register.html.twig', array(
                'form' => $form->createView(),
                'modal' => false
            ));
        }
    }
}