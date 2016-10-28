<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/07/2016
 * Time: 16:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class EventInvitationController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event-invitation", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventInvitationController extends Controller
{
    /**
     * Action pour afficher sur un événement à partir d'un token eventInvitation qui est alors enregistré en session avant la redirection vers la page d'affichage de l'événement
     *
     * @Route("/show/{token}", name="displayEventInvitation" )
     * @ParamConverter("eventInvitation", class="AppBundle:Event\EventInvitation")
     */
    public function displayEventInvitationAction(EventInvitation $eventInvitation = null, Request $request)
    {
        if ($eventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
            return $this->redirectToRoute("home");
        }
        $this->denyAccessUnlessGranted(EventInvitationVoter::EDIT, $eventInvitation);
        $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY, $eventInvitation->getToken());
        return $this->redirectToRoute("displayEvent", array('token' => $eventInvitation->getEvent()->getToken()));
    }

    /**
     * @Route("/disconnect/{eventToken}", name="disconnectEventInvitation" )
     */
    public function disconnectEventInvitationAction($eventToken, Request $request)
    {
        if ($request->hasSession()) {
            $request->getSession()->remove(EventInvitationManager::TOKEN_SESSION_KEY);
        }
        return $this->redirectToRoute("displayEvent", array('token' => $eventToken));
    }

    /**
     * @Route("/cancel/{eventInvitationTokenToCancel}", name="cancelEventInvitation" )
     * @ParamConverter("eventInvitationToCancel", class="AppBundle:Event\EventInvitation", options={"mapping": {"eventInvitationTokenToCancel":"token"}})
     */
    public function cancelEventInvitationAction(EventInvitation $eventInvitationToCancel, Request $request)
    {
        $userEventInvitation = null;
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        if ($eventInvitationToCancel != null) {
            // Get the UserInvitation
            $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($eventInvitationToCancel->getEvent(), false, false, $this->getUser());
        }

        // Only creator/admnsitrators can cancel an EventInvitation
        if ($this->isGranted(EventInvitationVoter::CANCEL, [$userEventInvitation, $eventInvitationToCancel])) {
            if ($eventInvitationManager->cancelEventInvitation($eventInvitationToCancel)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("global.success.data_saved");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("global.success.data_saved"));
                    return $this->redirectToRoute("displayEvent", array("token" => $eventInvitationToCancel->getEvent()->getToken()));
                }
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_cancel");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_cancel"));
            if ($eventInvitationToCancel != null) {
                return $this->redirectToRoute("displayEvent", array("token" => $eventInvitationToCancel->getEvent()->getToken()));
            } else {
                return $this->redirectToRoute("home");
            }
        }
    }

    /**
     * @Route("/show-all", name="displayUserEvents")
     */
    public function displayUserEventsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $userEventInvitations = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser());
        return $this->render("@App/EventInvitation/user_event_invitations.html.twig", array("eventInvitations" => $userEventInvitations));

    }

}