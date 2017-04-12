<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/10/2016
 * Time: 14:52
 */

namespace ATUserBundle\Mailer;


use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Mailer\TwigSwiftMailer;

class AtTwigSiwftMailer extends TwigSwiftMailer
{
    /**
     * Send email to the old owner of the AppUserEmail
     * @param AccountUser $user
     * @param $concernedEmail
     */
    public function sendLoseAppUserEmail(AccountUser $user, $concernedEmail)
    {
        $context = array(
            "username" => $user->getDisplayableName(true),
            "email" => $concernedEmail
        );
        $this->sendMessage("@ATUser/Registration/email_lossAppUserEmail.txt.twig", $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

}