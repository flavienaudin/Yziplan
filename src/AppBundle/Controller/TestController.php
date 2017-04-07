<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/11/2016
 * Time: 14:12
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Notifications\Notification;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestController
 * @package AppBundle\Controller
 *
 * @Route("/{_locale}/test", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class TestController extends Controller
{

    /**
     * @Route("/action")
     */
    public function testAction()
    {
        if ($this->get('kernel')->getEnvironment() == "dev") {
            // Test here
            return new Response();
        }
        return $this->render("@App/Test/test_sendInvitationEmail.html.twig", ['message' => "MAUVAIS ENVIRONNEMENT"]);

    }

}