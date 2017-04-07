<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 23/03/2017
 * Time: 12:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Notifications\Notification;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventInvitationVoter;
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
     * Action pour mettre a jour la date de dernière visit d'une invitation
     *
     * @Route("/visited/{token}", name="updateLastVisitAtEventInvitation" )
     * @ParamConverter("eventInvitation", class="AppBundle:Event\EventInvitation")
     */
    public function updateLastVisitEventInvitationAction(EventInvitation $eventInvitation = null, Request $request)
    {
        if ($eventInvitation == null) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access");
                $data[AppJsonResponse::REDIRECT] = $this->generateUrl('home');
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
                return $this->redirectToRoute("home");
            }
        }
        $this->denyAccessUnlessGranted(EventInvitationVoter::EDIT, $eventInvitation);
        $this->get("at.manager.event_invitation")->updateLastVisit($eventInvitation);
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::DATA]["last_visit_ad"] = $eventInvitation->getLastVisitAt();
            return new AppJsonResponse($data, Response::HTTP_OK);
        } else {
            return $this->redirectToRoute("displayEventInvitation", array('token' => $eventInvitation->getToken()));
        }
    }

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
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access");
                $data[AppJsonResponse::REDIRECT] = $this->generateUrl('home');
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
                return $this->redirectToRoute("home");
            }
        } else {
            $this->get('at.manager.notification')->markAllView($userEventInvitation);
            if ($request->isXmlHttpRequest()) {
                return new AppJsonResponse(array(), Response::HTTP_OK);
            } else {
                return $this->redirectToRoute("displayEvent", array('token' => $currentEvent->getToken()));
            }
        }
    }


    /**
     * @Route("/mark-view/{id}", name="markAsView")
     * @ParamConverter("notification", class="AppBundle:Notifications\Notification")
     */
    public function markOneView(Notification $notification, Request $request)
    {
        /////////////////////////////////////
        // User EventInvitation management //
        /////////////////////////////////////
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($notification->getEventInvitation()->getEvent(), false, false, $this->getUser());
        if ($userEventInvitation == null) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access");
                $data[AppJsonResponse::REDIRECT] = $this->generateUrl('home');
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
                return $this->redirectToRoute("home");
            }
        } else {
            $notifId = $notification->getId();
            $this->get('at.manager.notification')->markAsView($notification);
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#notification_' . $notifId] = '';
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                return $this->redirectToRoute("displayEvent", array('token' => $userEventInvitation->getEvent()->getToken()));
            }
        }
    }
}