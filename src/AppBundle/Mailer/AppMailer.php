<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/04/2017
 * Time: 10:42
 */

namespace AppBundle\Mailer;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Notifications\Notification;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Utils\enum\NotificationTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppMailer
{

    const SEND_IMMEDIATLY = 1;
    const SEND_SPOLL_QUICKLY = 2;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var \Swift_Mailer */
    protected $spool_mailer;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var array */
    protected $parameters;

    /**
     * TwigSwiftMailer constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param \Swift_Mailer $spool_mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment $twig
     * @param array $parameters
     */
    public function __construct(\Swift_Mailer $mailer, \Swift_Mailer $spool_mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->spool_mailer = $spool_mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    /**
     * Fonction finale d'envoi d'un e-mail défini par les paramètres
     *
     * @param string $templateName Le template TWIG à utiliser pour l'envoi de l'email
     * @param array $context Les variables nécessaire à la constitution du message avec le template
     * @param array $fromEmail L'e-mail de l'expéditeur
     * @param string $toEmail L'e-mail du destinataire
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail, $when = self::SEND_IMMEDIATLY)
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);

        $htmlBody = '';

        if ($template->hasBlock('body_html', $context)) {
            $htmlBody = $template->renderBlock('body_html', $context);
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html');
            $message->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        // send email with the good mailer
        switch ($when) {
            case self::SEND_SPOLL_QUICKLY:
                $this->spool_mailer->send($message);
                break;
            case self::SEND_IMMEDIATLY:
            default:
                $this->mailer->send($message);
                break;
        }
    }

    /*******************************************************************************************************************
     *      Fonctions spécifiques d'envoi des emails
     *******************************************************************************************************************/

    /**
     * Send email to the AppUserEmail.email with confirmationToken to validate the email
     * @param AppUserEmail $appUserEmail
     */
    public function sendConfirmationAppUserEmailMessage(AppUserEmail $appUserEmail)
    {
        $url = $this->router->generate('confirmAppUserEmailAssociation', array('token' => $appUserEmail->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $context = array(
            "username" => $appUserEmail->getApplicationUser()->getDisplayableName(true, false),
            "email" => $appUserEmail->getEmail(),
            'confirmationUrl' => $url
        );
        $this->sendMessage("@ATUser/Registration/email_confirmationAppUserEmail.txt.twig", $context, $this->parameters['from_email']['confirmation'], $appUserEmail->getEmail());
    }

    /**
     * Envoi d'un e-mail d'invitation à un événement
     * @param EventInvitation $eventInvitation
     * @param string|null $message
     * @return bool
     */
    public function sendEventInvitationEmail(EventInvitation $eventInvitation, $message = null)
    {
        if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
            $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
        } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
            $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
        }
        if (!empty($emailTo)) {
            $organizerNames = '';
            /** @var EventInvitation $organizer */
            foreach ($eventInvitation->getEvent()->getOrganizers() as $organizer) {
                $organizerNames .= (!empty($organizerNames) ? ', ' : '') . $organizer->getDisplayableName(true, true);
            }
            $context = array(
                "recipient_name" => $eventInvitation->getDisplayableName(true, false),
                "eventInvitation" => $eventInvitation,
                'message' => $message,
                'organizerNames' => $organizerNames
            );
            $this->sendMessage("@App/EventInvitation/emails/invitation.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo, self::SEND_IMMEDIATLY);
            return true;
        }
        return false;
    }

    /**
     * @param EventInvitation $eventInvitation
     * @param null $message
     * @return bool
     */
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
            $organizerNames = '';
            /** @var EventInvitation $organizer */
            foreach ($eventInvitation->getEvent()->getOrganizers() as $organizer) {
                $organizerNames .= (!empty($organizerNames) ? ', ' : '') . $organizer->getDisplayableName(true, true);
            }
            $context = array(
                "recipient_name" => $eventInvitation->getDisplayableName(true, false),
                "eventInvitation" => $eventInvitation,
                'message' => $message,
                'organizerNames' => $organizerNames
            );
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
            $organizerNames = '';
            /** @var EventInvitation $organizer */
            foreach ($eventInvitation->getEvent()->getOrganizers() as $organizer) {
                $organizerNames .= (!empty($organizerNames) ? ', ' : '') . $organizer->getDisplayableName(true, true);
            }
            $context = array(
                "recipient_name" => $eventInvitation->getDisplayableName(true, false),
                "eventInvitation" => $eventInvitation,
                'organizerNames' => $organizerNames
            );
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
            $organizerNames = '';
            /** @var EventInvitation $organizer */
            foreach ($eventInvitation->getEvent()->getOrganizers() as $organizer) {
                $organizerNames .= (!empty($organizerNames) ? ', ' : '') . $organizer->getDisplayableName(true, true);
            }
            $context = array(
                "recipient_name" => $eventInvitation->getDisplayableName(true, false),
                "eventInvitation" => $eventInvitation,
                "message" => $message,
                'organizerNames' => $organizerNames
            );
            $this->sendMessage("@App/Event/partials/invitations/invitations_send_message_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
            return true;
        }
        return false;
    }

    public function sendNewNotificationEmail(EventInvitation $eventInvitation, Notification $notification, EventInvitation $triggerer)
    {
        if ($eventInvitation->getApplicationUser() != null) {
            if ($eventInvitation->getApplicationUser()->getAccountUser() != null) {
                $emailTo = $eventInvitation->getApplicationUser()->getAccountUser()->getEmail();
            } elseif (count($eventInvitation->getApplicationUser()->getAppUserEmails()) > 0) {
                $emailTo = $eventInvitation->getApplicationUser()->getAppUserEmails()->first()->getEmail();
            }
        }
        if (!empty($emailTo)) {
            $context = array(
                'recipient_name' => $eventInvitation->getDisplayableName(true, false),
                'eventInvitation' => $eventInvitation,
                'notification' => $notification,
                'triggerer' => $triggerer);
            switch ($notification->getType()) {
                case NotificationTypeEnum::POST_COMMENT:
                    $this->sendMessage("@App/Notifications/emails/notification_new_comment_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
                    break;
                case NotificationTypeEnum::ADD_MODULE:
                    $this->sendMessage("@App/Notifications/emails/notification_new_module_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
                    break;
                case NotificationTypeEnum::ADD_POLL_PROPOSAL:
                    $this->sendMessage("@App/Notifications/emails/notification_new_pollProposal_email.html.twig", $context, $this->parameters['from_email']['yziplan'], $emailTo);
                    break;
            }

            return true;
        }
        return false;
    }

    /**
     * @param string $titre
     * @param string $message
     * @return bool
     */
    public function sendSuggestionEmail($titre = "sans titre", $message = "sans message")
    {
        $context = array('recipient_name' => 'Contact Yziplan', 'titre' => $titre, 'message' => $message);
        $this->sendMessage(":Email:suggestion_email.html.twig", $context, $this->parameters['from_email']['yziplan'], 'contact@yziplan.fr', self::SEND_SPOLL_QUICKLY);
        return true;
    }
}