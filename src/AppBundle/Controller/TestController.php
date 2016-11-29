<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/11/2016
 * Time: 14:12
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\EventInvitation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TestController
 * @package AppBundle\Controller
 *
 * @Route("/{_locale}/test", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class TestController extends Controller
{

    /**
     * @Route("/sendInvitation")
     */
    public function sendInvitationEmail()
    {
        if ($this->get('kernel')->getEnvironment() == "dev") {
            $eventInvitation = $this->get('doctrine')->getRepository(EventInvitation::class)->find(14);

            /*return $this->render("@App/EventInvitation/emails/invitation.html.twig", array(
                'eventInvitation' => $eventInvitation
            ));*/

            $this->get("app.mailer.twig_swift")->sendEventInvitationEmail($eventInvitation);
            return new Response("email envoyé");
        }
        return new Response("WRONG ENVIRONMENT");
    }

}