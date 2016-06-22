<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;


use AppBundle\Entity\AppUser;
use AppBundle\Entity\Module;
use AppBundle\Form\EventFormType;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        return $this->render('AppBundle:Event/proto:test-event.html.twig');
    }

    /**
     * @Route("/{_locale}/evenement/{token}/{tokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayEvent")
     */
    public function displayEventAction($token=null, $tokenEdition=null, Request $request)
    {
        $eventManager = $this->get('at.manager.event');

        if ($eventManager->retrieveEvent($token)) {
            $currentEvent = $eventManager->getEvent();
            
            $allowEdit = (($tokenEdition == $currentEvent->getTokenEdition()) && $this->isGranted(EventVoter::EDITER, $currentEvent))
                || (($tokenEdition == null) && $this->isGranted(EventVoter::EDITER, $currentEvent));

            $eventForm = null;
            if ($allowEdit) {
                $eventForm = $eventManager->initEventForm($this->getUser());
                $eventForm->handleRequest($request);
                if ($eventForm->isValid()) {
                    $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator.default')->trans("event.success.message.creation"));
                    return $this->redirectToRoute("displayEvent", array('token' => $currentEvent->getToken()));
                }
            }

            return $this->render('AppBundle:Event:event.html.twig', array(
                'event' => $currentEvent,
                'allowEdit' => $allowEdit,
                'eventForm' => ($eventForm!=null?$eventForm->createView():null)
            ));
        }
        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator.default')->trans("event.error.message.unauthorized_access"));
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{_locale)/app-user/{appUserId}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="appUser")
     * @ParamConverter("appUser", class="AppBundle:AppUser", options={"id":"appUserId"})
     * @ Security(is_granted('APPUSER_SHOW', appUser)") // TODO definir AppUserAuthorizationVoter
     */
    public function displayAppUserPartialAction(AppUser $appUser, Request $request)
    {
        return $this->render("@App/Event/partials/appUserCard.html.twig", [
            "appUser" => $appUser
        ]);
    }

    /**
     * @Route("/{_locale)/module/{moduleId}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayModule")
     * @ParamConverter("module", class="AppBundle:Module", options={"id":"moduleId"})
     * @ Security(is_granted('MODULE_SHOW', module)") // TODO definir ModuleAuthorizationVoter
     */
    public function displayModulePartialAction(Module $module, Request $request)
    {
        if ($module->getPollModule() != null) {
            return $this->render("@App/Event/module/displayPollModule.html.twig", [
                "module" => $module
            ]);
        } elseif ($module->getExpenseModule() != null) {
            return $this->render("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module
            ]);
        } else {
            return $this->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module
            ]);
        }
    }
}