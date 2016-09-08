<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/09/2016
 * Time: 10:59
 */

namespace AppBundle\Mailer;


use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\RouterInterface;


class ATTwigSwiftMailer
{
    /** @var \Swift_Mailer $mailer */
    protected $mailer;
    /** @var RouterInterface */
    private $router;
    /** @var EngineInterface */
    private $templating;
    /** @var Translator */
    private $translator;

    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, Translator $translator)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
    }


    public function sendInvitation()
    {
        //$this->sendMessage("Invitation","");
    }


    /**
     * @param string $subject
     * @param array|string $fromEmails
     * @param array|string $toEmails
     * @param string $htmlTemplateName
     * @param array $htmlParameters
     * @param string $plainTemplateName
     * @param array $plainParameters
     */
    protected function sendMessage($subject, $fromEmails, $toEmails, $htmlTemplateName, $htmlParameters)
    {
        /** @var \Swift_Message $message */
        $message = \Swift_Message::newInstance($subject);
        $message
            ->setFrom($fromEmails)
            ->setTo($toEmails)
            ->setBody(
                $this->templating->render(
                    $htmlTemplateName,
                    $htmlParameters
                ),
                'text/html'
            );
        $message->addPart($this->templating->render(
            $htmlTemplateName,
            $htmlParameters
        ),
            'text/plain');
        $this->mailer->send($message, $failedRecipients);
    }
}