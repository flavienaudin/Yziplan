<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;


use AppBundle\Entity\AppUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\Module;
use AppBundle\Form\EventFormType;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    public function displayEventAction($token = null, $tokenEdition = null, Request $request)
    {
        $eventManager = $this->get('at.manager.event');

        if ($eventManager->retrieveEvent($token)) {
            $currentEvent = $eventManager->getEvent();

            if (!empty($tokenEdition)) {
                /** @var SessionInterface $userSession */
                $request->getSession()->set("tokenEdition", $tokenEdition);
            } else {
                $request->getSession()->remove("tokenEdition");
            }

            return $this->render('AppBundle:Event:event.html.twig', array(
                'event' => $currentEvent
            ));
        }
        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator.default')->trans("event.error.message.unauthorized_access"));
        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/{_locale)/event-header-card", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayEventHeaderCard")
     * @ Security(is_granted('APPUSER_SHOW', appUser)") // TODO definir AppUserAuthorizationVoter
     */
    public function displayEventHeaderCardAction(Event $event = null, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        if ($event == null) {
            $event = new Event();
        }
        $eventManager->setEvent($event);
        $allowEdit = false;
        $eventForm = null;
        if ($request->hasSession()) {
            $tokenEdition = $request->getSession()->get("tokenEdition");
            $allowEdit = $this->isGranted(EventVoter::EDITER, $event) && ($tokenEdition === $event->getTokenEdition() || $tokenEdition === null);

            if ($allowEdit) {
                /** @var Form $eventForm */
                $eventForm = $eventManager->initEventForm($this->getUser());
                $eventForm->handleRequest($request);
                if ($eventForm->isValid()) {
                    $event = $eventManager->treatEventFormSubmission($eventForm);
                    return $this->redirectToRoute('displayEvent', array('token' => $event->getToken(), 'tokenEdition' => $event->getTokenEdition()));
                }
            }
        }
        return $this->render("@App/Event/partials/eventHeaderCard.html.twig", ["event" => $event,
            "allowEdit" => $allowEdit,
            'eventForm' => ($eventForm != null ? $eventForm->createView() : null)]);
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