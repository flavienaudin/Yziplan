<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/10/2016
 * Time: 14:52
 */

namespace ATUserBundle\Mailer;


use AppBundle\Entity\User\AppUserEmail;
use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AtTwigSiwftMailer extends TwigSwiftMailer
{

    /**
     * Send email when "OAuth" account initialize its password
     * @param UserInterface $user
     */
    public function sendInitializePasswordMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['resetting'];
        $url = $this->router->generate('init_password_initialize', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], $user->getEmail());
    }

    /**
     * Send email to the old owner of the AppUserEmail
     * @param AccountUser $user
     * @param $concernedEmail
     */
    public function sendLoseAppUserEmail(AccountUser $user, $concernedEmail){
        $context = array(
            "username" => $user->getDisplayableName(),
            "email" => $concernedEmail
        );
        $this->sendMessage("@ATUser/Registration/email_lossAppUserEmail.txt.twig", $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    /**
     * Send email to the AppUserEmail.email with confirmationToken to validate the email
     * @param AppUserEmail $appUserEmail
     */
    public function sendConfirmationAppUserEmailMessage(AppUserEmail $appUserEmail)
    {
        $url = $this->router->generate('confirmAppUserEmailAssociation', array('token' => $appUserEmail->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $context = array(
            "username" => $appUserEmail->getApplicationUser()->getDisplayableName(),
            "email" => $appUserEmail->getEmail(),
            'confirmationUrl' => $url
        );
        $this->sendMessage("@ATUser/Registration/email_confirmationAppUserEmail.txt.twig", $context, $this->parameters['from_email']['confirmation'], $appUserEmail->getEmail());
    }

}