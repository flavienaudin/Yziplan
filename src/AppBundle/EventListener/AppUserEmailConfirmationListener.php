<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/10/2016
 * Time: 12:28
 */

namespace AppBundle\EventListener;


use AppBundle\AppEvents;
use AppBundle\Entity\User\AppUserEmail;
use ATUserBundle\Mailer\AtTwigSiwftMailer;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppUserEmailConfirmationListener implements EventSubscriberInterface
{
    /** @var AtTwigSiwftMailer $mailer */
    private $mailer;
    private $tokenGenerator;
    private $router;

    /**
     * EmailConfirmationListener constructor.
     *
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UrlGeneratorInterface $router
     */
    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, UrlGeneratorInterface $router)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AppEvents::APPUSEREMAIL_ADD_SUCCESS => 'onAddSuccess',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onAddSuccess(FormEvent $event)
    {
        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $event->getForm()->getData();
        if (null === $appUserEmail->getConfirmationToken()) {
            $appUserEmail->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $this->mailer->sendConfirmationAppUserEmailMessage($appUserEmail);
    }
}