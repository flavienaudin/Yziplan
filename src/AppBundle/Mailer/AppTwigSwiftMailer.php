<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/10/2016
 * Time: 14:52
 */

namespace AppBundle\Mailer;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Notifications\Notification;
use FOS\UserBundle\Mailer\TwigSwiftMailer;


class AppTwigSwiftMailer extends TwigSwiftMailer
{
    public function loadYziplanFromEmail()
    {
        $this->parameters['from_email']['yziplan']['contact@yziplan.fr'] = "Yziplan";
    }

    public function sendEventInvitationEmail(EventInvitation $eventInvitation, $message = null)
    {
        if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
            $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
        } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
            $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation, 'message' => $message);
            $this->sendMessage("@App/EventInvitation/emails/invitation.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;
        }
        return false;
    }

    public function sendCancellationEmail(EventInvitation $eventInvitation, $message = null)
    {
        if ($eventInvitation->getApplicationUser() != null) {
            if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
                $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
            } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
                $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
            }
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation, 'message' => $message);
            $this->sendMessage("@App/Event/partials/invitations/invitations_send_cancellation_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;
        }
        return false;
    }


    public function sendRecapEventInvitationEmail(EventInvitation $eventInvitation)
    {
        if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
            $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
        } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
            $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation);
            $this->sendMessage("@App/EventInvitation/emails/recap_invitation.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;

        }
        return false;
    }

    public function sendMessageEmail(EventInvitation $eventInvitation, $message = null)
    {
        if ($eventInvitation->getApplicationUser() != null) {
            if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
                $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
            } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
                $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
            }
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation, 'message' => $message);
            $this->sendMessage("@App/Event/partials/invitations/invitations_send_message_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;
        }
        return false;
    }

    public function sendSuggestionEmail($titre = "sans titre", $message = "sans message")
    {
        $context = array('titre' => $titre, 'message' => $message);
        $this->sendMessage(":Email:suggestion_email.html.twig", $context, $this->parameters['from_email']['yziplan'], 'contact@yziplan.fr');
        return true;
    }


    public function sendNewCommentNotificationEmail(EventInvitation $eventInvitation, Notification $notification, EventInvitation $triggerer)
    {
        if ($eventInvitation->getApplicationUser() != null) {
            if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
                $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
            } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
                $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
            }
        }
        if (!empty($emailTo)) {
            $context = array("eventInvitation" => $eventInvitation, 'notification' => $notification, 'triggerer' => $triggerer);
            $this->sendMessage("@App/Notifications/emails/notification_new_comment_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;
        }
        return false;
    }
}