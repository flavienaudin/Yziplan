<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/06/2016
 * Time: 16:21
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event;
use AppBundle\Entity\Module;
use AppBundle\Security\EventVoter;
use AppBundle\Security\ModuleVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModuleController extends Controller
{

    /**
     * @Route("/{_locale}/add-event-module/{token}/{type}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="addEventModule")
     * @ParamConverter("event", class="AppBundle:Event")
     */
    public function addEventModuleAction(Event $event, $type, Request $request)
    {
        $this->denyAccessUnlessGranted(EventVoter::ADD_EVENT_MODULE, $event);
        $eventManager = $this->get("at.manager.event");
        $eventManager->setEvent($event);
        $module = $eventManager->addModule($type);
        if ($request->isXmlHttpRequest()) {
            $moduleManager = $this->get('at.manager.module');
            return new JsonResponse(array('htmlContent' => $moduleManager->displayModulePartial($module, true)), Response::HTTP_OK);
        } else {
            $event = $eventManager->getEvent();
            return $this->redirect($this->generateUrl('displayEvent', array('token' => $event->getToken(), 'tokenEdition' => $event->getTokenEdition())) . '#module-' . $module->getToken());
        }
    }

    /**
     * @Route("/{_locale}/remove-event-module/{tokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="removeEventModule")
     * @ParamConverter("module", class="AppBundle:Module", options={"tokenEdition":"tokenEdition"})
     */
    public function removeEventModuleAction(Module $module, Request $request)
    {
        $this->denyAccessUnlessGranted(ModuleVoter::DELETE, $module);
        $moduleManager = $this->get("at.manager.module");
        $moduleManager->setModule($module);
        $module = $moduleManager->removeModule();
        if ($request->isXmlHttpRequest()) {
            $responseData['actionResult'] = true;
            return new JsonResponse($responseData, Response::HTTP_OK);
        } else {
            return $this->redirectToRoute('displayEvent', array('token' => $module->getEvent()->getToken(), 'tokenEdition' => $module->getEvent()->getTokenEdition()));
        }
    }

}