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
        }else{
            return $this->render("@App/Event/module/displayModule.html.twig", [
                "module" => $module
            ]);
        }
    }
}