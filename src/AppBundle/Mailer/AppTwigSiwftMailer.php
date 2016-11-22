<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/10/2016
 * Time: 14:52
 */

namespace AppBundle\Mailer;


use AppBundle\Entity\Event\EventInvitation;
use FOS\UserBundle\Mailer\TwigSwiftMailer;


class AppTwigSiwftMailer extends TwigSwiftMailer
{

    public function sendEventInvitationEmail(EventInvitation $eventInvitation)
    {
        if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
            $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
        } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
            $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation);
            $this->sendMessage("@App/EventInvitation/emails/invitation.html.twig", $context, $this->parameters['from_email']['confirmation'], $emailTo);
            return true;

        }
        return false;
    }
}