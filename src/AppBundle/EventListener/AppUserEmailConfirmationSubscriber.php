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
use AppBundle\Event\AppUserEmailEvent;
use AppBundle\Mailer\AppMailer;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppUserEmailConfirmationSubscriber implements EventSubscriberInterface
{
    /** @var AppMailer $mailer */
    private $mailer;
    /** @var TokenGeneratorInterface */
    private $tokenGenerator;
    /** @var UrlGeneratorInterface */
    private $router;

    /**
     * EmailConfirmationListener constructor.
     *
     * @param AppMailer $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UrlGeneratorInterface $router
     */
    public function __construct(AppMailer $mailer, TokenGeneratorInterface $tokenGenerator, UrlGeneratorInterface $router)
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
     * @param AppUserEmailEvent $event
     */
    public function onAddSuccess(AppUserEmailEvent $event)
    {
        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $event->getAppUserEmail();
        if (null === $appUserEmail->getConfirmationToken()) {
            $appUserEmail->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $this->mailer->sendConfirmationAppUserEmailMessage($appUserEmail);
    }
}