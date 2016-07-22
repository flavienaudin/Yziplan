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
use AppBundle\Utils\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModuleController extends Controller
{

    /**
     * @Route("/{_locale}/add-event-module/{token}/{type}/{tokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="addEventModule")
     * @ParamConverter("event", class="AppBundle:Event", options={"exclude": {"tokenEdition"}})
     */
    public function addEventModuleAction(Event $event, $type, $tokenEdition = null, Request $request)
    {
        $this->denyAccessUnlessGranted(EventVoter::ADD_EVENT_MODULE, array($event, $tokenEdition));
        $eventManager = $this->get("at.manager.event");
        $eventManager->setEvent($event);
        $module = $eventManager->addModule($type);
        if ($request->isXmlHttpRequest()) {
            if ($module != null) {
                $moduleManager = $this->get('at.manager.module');
                $userEventInvitation = $this->get('at.manager.event_invitation')->retrieveUserEventInvitation($event, false, false, $this->getUser());
                return new JsonResponse(array('htmlContent' => $moduleManager->displayModulePartial($module, $userEventInvitation->getModuleInvitationForModule($module))), Response::HTTP_OK);
            } else {
                $responseData['messages'][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('module.error.message.add');
                return new JsonResponse($responseData, Response::HTTP_BAD_REQUEST);
            }
        } else {
            if ($module == null) {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('module.error.message.add'));
            }
            return $this->redirect($this->generateUrl('displayEvent', array(
                    'token' => $event->getToken(),
                    'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($event, $tokenEdition)) ? $event->getTokenEdition() : null)
                )) . ($module != null ? '#module-' . $module->getToken():'')
            );

        }
    }

    /**
     * @Route("/{_locale}/remove-event-module/{tokenEdition}/{eventTokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="removeEventModule")
     * @ParamConverter("module", class="AppBundle:Module", options={"exclude": {"eventTokenEdition"}})
     */
    public function removeEventModuleAction(Module $module, $eventTokenEdition = null, Request $request)
    {
        $userEventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($module->getEvent(), false, false, $this->getUser());
        $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
        if ($request->isXmlHttpRequest()) {
            if ($this->isGranted(ModuleVoter::DELETE, array($module, $userModuleInvitation))) {
                $moduleManager = $this->get("at.manager.module");
                $moduleManager->setModule($module);
                $moduleManager->removeModule();
                $responseData['actionResult'] = true;
                return new JsonResponse($responseData, Response::HTTP_OK);
            } else {
                $responseData['messages'][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $this->denyAccessUnlessGranted(ModuleVoter::DELETE, array($module, $userModuleInvitation));
            $moduleManager = $this->get("at.manager.module");
            $moduleManager->setModule($module);
            $moduleManager->removeModule();
            return $this->redirectToRoute('displayEvent', array(
                'token' => $module->getEvent()->getToken(),
                'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($module->getEvent(), $eventTokenEdition)) ? $module->getEvent()->getTokenEdition() : null)
            ));
        }
    }

}