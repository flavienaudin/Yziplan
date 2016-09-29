<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/07/2016
 * Time: 16:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\EventInvitation;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\enum\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class EventInvitationController extends Controller
{
    /**
     * Action pour afficher sur un événement à partir d'un token eventInvitation qui est alors enregistré en session avant la redirection vers la page d'affichage de l'événement
     *
     * @Route("/{_locale}/invitation/{token}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayEventInvitation" )
     * @ParamConverter("eventInvitation", class="AppBundle:EventInvitation")
     */
    public function displayEventInvitationAction(EventInvitation $eventInvitation = null, Request $request)
    {
        if ($eventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.error.message.unauthorized_access"));
            return $this->redirectToRoute("home");
        }
        $this->denyAccessUnlessGranted(EventInvitationVoter::EDIT, $eventInvitation);
        $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY, $eventInvitation->getToken());
        return $this->redirectToRoute("displayEvent", array(
            'token' => $eventInvitation->getEvent()->getToken(),
            'tokenEdition' => ($eventInvitation->getEvent()->getCreator() == $eventInvitation ? $eventInvitation->getEvent()->getTokenEdition() : null)));
    }

    /**
     * @Route("/{_locale}/disconnect-invitation/{token}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="disconnectEventInvitation" )
     */
    public function disconnectEventInvitationAction($token, Request $request)
    {
        if ($request->hasSession()) {
            $request->getSession()->remove(EventInvitationManager::TOKEN_SESSION_KEY);
            $request->getSession()->remove(EventInvitationManager::TOKEN_EDITION_SESSION_KEY);
            $request->getSession()->remove(EventManager::TOKEN_EDITION_SESSION_KEY);
        }
        return $this->redirectToRoute("displayEvent", array('token' => $token));
    }

    /**
     * @Route("/{_locale}/cancel-invitation/{eventInvitationToken}/{eventTokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="cancelEventInvitation" )
     * @ParamConverter("eventInvitation", class="AppBundle:EventInvitation", options={"mapping": {"eventInvitationToken":"token"}})
     */
    public function cancelEventInvitationAction(EventInvitation $eventInvitation, $eventTokenEdition = null, Request $request)
    {
        if ($this->isGranted(EventVoter::EDIT, array($eventInvitation->getEvent(), $eventTokenEdition))) {
            $eventInvitationManager = $this->get("at.manager.event_invitation");
            if ($eventInvitationManager->cancelEventInvitation($eventInvitation)) {
                if ($request->isXmlHttpRequest()) {
                    $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("global.success.data_saved");
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("global.success.data_saved"));
                    return $this->redirectToRoute("displayEvent", array(
                        "token" => $eventInvitation->getEvent()->getToken(),
                        "tokenEdition" => $eventTokenEdition));
                }
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data['messages'][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.error.message.unauthorized_access");
            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.error.message.unauthorized_access"));
            if ($eventInvitation != null) {
                return $this->redirectToRoute("displayEvent", array("token" => $eventInvitation->getEvent()->getToken()));
            } else {
                return $this->redirectToRoute("home");
            }
        }
    }


    /**
     * @Route("/{_locale}/mes-evenements", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayUserEvents")
     */
    public function displayUserEventsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $userEventInvitations = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser());
        return $this->render("@App/EventInvitation/user_event_invitations.html.twig", array(
            "eventInvitations" => $userEventInvitations
        ));

    }

}