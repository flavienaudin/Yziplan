<?php

namespace ATUserBundle\EventListener;

use ATUserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;

class ResettingListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onResettingResetSuccess'
        );
    }

    public function onResettingResetSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if ($user instanceof User) {
            $user->setPasswordKnown(true);
        }
    }
}
