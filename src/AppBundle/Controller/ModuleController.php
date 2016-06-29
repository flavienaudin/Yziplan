<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/06/2016
 * Time: 16:21
 */

namespace AppBundle\Controller;


use AppBundle\Entity\enum\ModuleStatus;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\PollModule;
use AppBundle\Manager\GenerateursToken;
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
     * @Route("/{_locale}/add-event-module/{eventTokenEdition}/{type}/", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="addEventModule")
     */
    public function addEventModuleAction($eventTokenEdition, $type, Request $request)
    {
        $eventManager = $this->get("at.manager.event");
        if ($eventManager->retrieveEvent($eventTokenEdition, 'tokenEdition')) {
            $event = $eventManager->getEvent();
            $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);
            $module = $eventManager->addModule($type);
            $event = $eventManager->getEvent();
            if ($request->isXmlHttpRequest()) {
                $view = $this->displayModulePartial($module, true, $request);
                return new JsonResponse(array('htmlContent' => $view), Response::HTTP_OK);
            } else {
                return $this->redirect($this->generateUrl('displayEvent', array('token' => $event->getToken(), 'tokenEdition' => $event->getTokenEdition())) . '#module-' . $module->getToken());
            }
        }

        if ($request->isXmlHttpRequest()) {
            $data['messages'][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.unauthorized_access");
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.unauthorized_access"));
            return $this->redirectToRoute('home');
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

    /**
     * @param Module $module Le module à afficher
     * @param $allowEdit boolean "true" si le module est éditable
     * @param Request $request
     * @return string La vue HTML sous forme de string
     */
    public function displayModulePartial(Module $module, $allowEdit, Request $request)
    {
        if ($module->getPollModule() != null) {
            return $this->renderView("@App/Event/module/displayPollModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit
            ]);
        } elseif ($module->getExpenseModule() != null) {
            return $this->renderView("@App/Event/module/displayExpenseModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit
            ]);
        } else {
            return $this->renderView("@App/Event/module/displayModule.html.twig", [
                "module" => $module,
                "allowEdit" => $allowEdit
            ]);
        }
    }

}