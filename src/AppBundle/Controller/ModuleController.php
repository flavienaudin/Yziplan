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
            if ($module != null) {
                $moduleManager = $this->get('at.manager.module');
                return new JsonResponse(array('htmlContent' => $moduleManager->displayModulePartial($module)), Response::HTTP_OK);
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
                    'tokenEdition' => ($this->isGranted(EventVoter::EDIT, $event) ? $event->getTokenEdition() : null)
                )) . '#module-' . $module->getToken()
            );

        }
    }

    /**
     * @Route("/{_locale}/remove-event-module/{tokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="removeEventModule")
     * @ParamConverter("module", class="AppBundle:Module", options={"tokenEdition":"tokenEdition"})
     */
    public function removeEventModuleAction(Module $module, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($this->isGranted(ModuleVoter::DELETE, $module)) {
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
            $this->denyAccessUnlessGranted(ModuleVoter::DELETE, $module);
            $moduleManager = $this->get("at.manager.module");
            $moduleManager->setModule($module);
            $moduleManager->removeModule();
            return $this->redirectToRoute('displayEvent', array(
                'token' => $module->getEvent()->getToken(),
                'tokenEdition' => ($this->isGranted(EventVoter::EDIT, $module->getEvent()) ? $module->getEvent()->getTokenEdition() : null)
            ));
        }
    }

}