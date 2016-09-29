<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/01/2016
 * Time: 13:27
 */

namespace ATUserBundle\EventListener;

use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InscriptionListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_INITIALIZE=> 'onRegistrationInitialise',
            FOSUserEvents::REGISTRATION_SUCCESS=> 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
        );
    }

    public function onRegistrationInitialise(GetResponseUserEvent $event)
    {
        $event->getUser()->setEmail(uniqid('email').'@at.at');
        $event->getUser()->setUsername(uniqid('username'));
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $form = $event->getForm();
        $email = $form['email']->getData();
        $user=$form->getData();
        $user->setEmail($email);
        $user->setUsername($user->getEmail());
    }

    public function onRegistrationConfirm(GetResponseUserEvent $event){
        $utilisateur = $event->getUser();
        if($utilisateur instanceof AccountUser) {
            $utilisateur->setPasswordKnown(true);
        }
    }
}