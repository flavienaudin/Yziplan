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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        if ($eventManager->retrieveEvent($token)) {
            $currentEvent = $eventManager->getEvent();
            $this->denyAccessUnlessGranted(EventVoter::DISPLAY, $currentEvent);

            if (!empty($tokenEdition)) {
                /** @var SessionInterface $userSession */
                $request->getSession()->set("tokenEdition", $tokenEdition);
            } else {
                $request->getSession()->remove("tokenEdition");
            }

            $eventForm = null;
            $allowEdit = $this->isGranted(EventVoter::EDIT, $currentEvent) && ($tokenEdition === $currentEvent->getTokenEdition() || $tokenEdition === null);

            if ($allowEdit) {
                /** @var Form $eventForm */
                $eventForm = $eventManager->initEventForm($this->getUser());
                $eventForm->handleRequest($request);
                if ($request->isXmlHttpRequest()) {
                    if ($eventForm->isSubmitted()) {
                        if ($eventForm->isValid()) {
                            $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                            $data[FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data['html'] = $this->renderView("@App/Event/partials/eventHeaderCard.html.twig", array('event' => $currentEvent, 'allowEdit' => $allowEdit, 'eventForm' =>
                                $eventForm->createView()));
                            return new JsonResponse($data);
                        } else {
                            $data["error"] = array();
                            foreach ($eventForm->getErrors(true) as $error) {
                                $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                            }
                            return new JsonResponse($data, 400);
                        }
                    }
                } else {
                    if ($eventForm->isValid()) {
                        $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken(), 'tokenEdition' => $currentEvent->getTokenEdition()));
                    }
                }
            }

            return $this->render('AppBundle:Event:event.html.twig', array(
                    'event' => $currentEvent,
                    "allowEdit" => $allowEdit,
                    'eventForm' => ($eventForm != null ? $eventForm->createView() : null))
            );

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
        return $this->render("@App/Event/partials/appUserCard.html.twig", array(
            "appUser" => $appUser
        ));
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