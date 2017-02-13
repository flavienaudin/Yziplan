<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/11/2016
 * Time: 14:12
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\EventInvitation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
            return $this->render("@App/Test/test_sendInvitationEmail.html.twig", ['message' => "ok"]);
        }
        return $this->render("@App/Test/test_sendInvitationEmail.html.twig", ['message' => "MAUVAIS ENVIRONNEMENT"]);

    }

}