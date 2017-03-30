<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 23/03/2017
 * Time: 12:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\Event;
use AppBundle\Manager\EventManager;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class NotificationsController
 * @package AppBundle\Controller
 * @Route("/{_locale}/notifications", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class NotificationsController extends Controller
{


    /**
     * @Route("/mark-all-view/{token}", name="markAll")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function markAllView(Event $currentEvent, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        $eventManager->setEvent($currentEvent);

        /////////////////////////////////////
        // User EventInvitation management //
        /////////////////////////////////////
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($currentEvent, false, false, $this->getUser());
        if ($userEventInvitation == null) {
            if ($request->isXmlHttpRequest()) {
                //TODO use AppJsonResponse::REDIRECT when merged with branch CancelEvent
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
                return $this->redirectToRoute("home");
            }
        } else {
            $eventInvitationManager->updateLastVisit($userEventInvitation);
            if ($request->isXmlHttpRequest()) {
                return new AppJsonResponse(array(), Response::HTTP_OK);
            } else {
                return $this->redirectToRoute("home");
            }
        }
    }
}