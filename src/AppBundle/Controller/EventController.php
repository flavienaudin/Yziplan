<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;


use AppBundle\Utils\FlashBagTypes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends Controller
{

    /**
     * @Route("/{_locale}/evenement-test", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="eventTest")
     */
    public function eventTestAction(Request $request)
    {
        return $this->render('AppBundle:Event:evenement.html.twig');
    }

    /**
     * @Route("/{_locale}/evenement/{token}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayEvent")
     */
    public function displayEventAction($token, Request $request)
    {
        $eventManager = $this->get('at.manager.event');

        if ($eventManager->retrieveEvent($token)) {
            $currentEvent = $eventManager->getEvent();
            return $this->render('AppBundle:Event:event.html.twig', array(
                'event' => $currentEvent
            ));
        }
        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator.default')->trans("event.error.message.unauthorized_access"));
        return $this->redirectToRoute('home');
    }
}