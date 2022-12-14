<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/01/2016
 * Time: 13:27
 */

namespace ATUserBundle\EventListener;

use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Event\AppUserEmailEvent;
use AppBundle\Manager\ApplicationUserManager;
use ATUserBundle\ATUserEvents;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Mailer\AtTwigSiwftMailer;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InscriptionListener implements EventSubscriberInterface
{
    /** @var ApplicationUserManager $applicationUserManager */
    private $applicationUserManager;
    /** @var AtTwigSiwftMailer $mailer */
    private $mailer;

    public function __construct(ApplicationUserManager $applicationUserManager, AtTwigSiwftMailer $mailer)
    {
        $this->applicationUserManager = $applicationUserManager;
        $this->mailer = $mailer;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialise',
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_CONFIRM => 'onRegistrationConfirm',
            ATUserEvents::OAUTH_REGISTRATION_SUCCESS => 'onOauthRegistrationSuccess'
        );
    }

    public function onRegistrationInitialise(GetResponseUserEvent $event)
    {
        $event->getUser()->setEmail(uniqid('email') . '@at.at');
        $event->getUser()->setUsername(uniqid('username'));
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $form = $event->getForm();
        $email = $form['email']->getData();
        /** @var AccountUser $user */
        $user = $form->getData();
        $user->setEmail($email);
        $user->setUsername($user->getEmail());
        $user->getApplicationUser()->getAppUserInformation()->setPublicName($form['publicName']->getData());

        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $this->applicationUserManager->findAppUserEmailByEmail($email);
        if ($appUserEmail == null) {
            // Pas d'AppUserEmail existant => on le cr???? et on le rattache au nouveau compte utilisateur
            $appUserEmail = new AppUserEmail();
            $appUserEmail->setEmail($email);
            $appUserEmail->setUseToReceiveEmail(true);
            $user->getApplicationUser()->addAppUserEmail($appUserEmail);
        } elseif ($appUserEmail->getApplicationUser()->getAccountUser() == null) {
            // AppUserEmail existant mais non rattach?? ?? un compte => On le rattache au nouveau compte utilisateur
            $this->applicationUserManager->mergeApplicationUsers($user->getApplicationUser(), $appUserEmail->getApplicationUser());

            $appUserEmail->setUseToReceiveEmail(true);
            $user->getApplicationUser()->addAppUserEmail($appUserEmail);
        } else {
            // AppUserEmail existant et rattach?? ?? un compte utilisateur sans en ??tre l'email principal => Envoi d'un e-mail d'avertisement
            // Le rattachement ne se fera qu'?? la confirmation du compte
            $oldOwner = $appUserEmail->getApplicationUser()->getAccountUser();
            $this->mailer->sendLoseAppUserEmail($oldOwner, $appUserEmail->getEmail());
        }
    }

    public function onRegistrationConfirm(GetResponseUserEvent $event)
    {
        $user = $event->getUser();
        if ($user instanceof AccountUser) {
            $user->setPasswordKnown(true);
            /** @var AppUserEmail $appUserEmail */
            $appUserEmail = $this->applicationUserManager->findAppUserEmailByEmail($user->getEmail());
            if ($appUserEmail != null && $appUserEmail->getApplicationUser()->getAccountUser() !== $user) {
                $appUserEmail->setUseToReceiveEmail(true);
                $appUserEmail->setConfirmationToken(null);
                $user->getApplicationUser()->addAppUserEmail($appUserEmail);
            }
        }
    }

    public function onOauthRegistrationSuccess(AppUserEmailEvent $event)
    {
        $appUserEmail = $event->getAppUserEmail();
        // AppUserEmail existant et il est rattach?? ?? un compte utilisateur sans en ??tre l'email principal => Envoi d'un e-mail d'avertisement
        // Le rattachement est effectif d??s maintenant car l'OAuth ne requiert pas de validation par email
        $oldOwner = $appUserEmail->getApplicationUser()->getAccountUser();
        $this->mailer->sendLoseAppUserEmail($oldOwner, $appUserEmail->getEmail());

    }
}