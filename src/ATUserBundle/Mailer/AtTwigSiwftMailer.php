<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/10/2016
 * Time: 14:52
 */

namespace ATUserBundle\Mailer;


use FOS\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AtTwigSiwftMailer extends TwigSwiftMailer
{
    
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
}