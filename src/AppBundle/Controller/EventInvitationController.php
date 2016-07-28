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
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Utils\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventInvitationController extends Controller
{
    /**
     * Action pour afficher sur un événement à partir d'un token eventInvitation qui est alors enregistré en session avant la rediection vers la page d'affichage de l'événement
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
        }
        return $this->redirectToRoute("displayEvent", array('token' => $token));
    }

}