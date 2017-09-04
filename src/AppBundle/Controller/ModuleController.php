<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/06/2016
 * Time: 16:21
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\Module;
use AppBundle\Security\EventVoter;
use AppBundle\Security\ModuleVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class ModuleController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event-module", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class ModuleController extends Controller
{

    /**
     * @Route("/add/{token}/{type}/{subtype}/{wizard}",  name="addEventModule")
     * @ParamConverter("event", class="AppBundle:Event\Event", options={"exclude":{"type","subtype"}})
     */
    public function addEventModuleAction(Event $event, $type, $subtype = null, $wizard = false, Request $request)
    {
        $userEventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        $this->denyAccessUnlessGranted(EventVoter::ADD_EVENT_MODULE, $userEventInvitation);
        if ( /*$request->server->get('HTTP_REFERER') != $this->generateUrl('wizardNewEventStep2', array('token' => $event->getToken()), UrlGenerator::ABSOLUTE_URL)*/
            !$wizard && ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
        ) {
            // Vérification serveur de la validité de l'invitation
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
            }
        } else {
            $eventManager = $this->get("at.manager.event");
            $moduleManager = $this->get('at.manager.module');
            $eventManager->setEvent($event);
            $module = $eventManager->addModule($type, $subtype, $userEventInvitation);
            if ($wizard) {
                $moduleManager->publishModule($userEventInvitation, $module, false);
            }
            if ($request->isXmlHttpRequest()) {
                if ($module != null) {
                    $data[AppJsonResponse::DATA]['moduleToken'] = $module->getToken();
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]['#eventModulesContainer'] =
                        $moduleManager->displayModulePartial($module, $userEventInvitation->getModuleInvitationForModule($module));
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $responseData[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('module.message.error.add');
                    return new AppJsonResponse($responseData, Response::HTTP_BAD_REQUEST);
                }
            } else {
                if ($module == null) {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('module.message.error.add'));
                }
                return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
            }
        }
    }

    /**
     * @Route("/publish/{token}",  name="publishEventModule")
     * @ParamConverter("module", class="AppBundle:Event\Module")
     */
    public function publishEventModuleAction(Module $module, Request $request)
    {
        $event = $module->getEvent();
        $userEventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
        $this->denyAccessUnlessGranted(ModuleVoter::PUBLISH, [$module, $userModuleInvitation]);
        if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
            // Vérification serveur de la validité de l'invitation
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
            }
        } else {
            $moduleManager = $this->get('at.manager.module');
            if ($moduleManager->publishModule($userEventInvitation, $module, true)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-' . $module->getToken()] =
                        $moduleManager->displayModulePartial($module, $userEventInvitation->getModuleInvitationForModule($module));
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans('module.message.success.publish'));
                    return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('module.message.error.publish');
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-' . $module->getToken()] =
                        $moduleManager->displayModulePartial($module, $userEventInvitation->getModuleInvitationForModule($module));
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('module.message.error.publish'));
                    return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
                }
            }
        }
    }

    /**
     * @Route("/refresh/{token}",  name="refreshEventModule")
     * @ParamConverter("module", class="AppBundle:Event\Module")
     */
    public function refreshEventModuleAction(Module $module, Request $request)
    {
        $event = $module->getEvent();
        $userEventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
        $this->denyAccessUnlessGranted(ModuleVoter::DISPLAY, [$module, $userModuleInvitation]);
        if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
            // Vérification serveur de la validité de l'invitation
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
            }
        } else {
            $moduleManager = $this->get('at.manager.module');
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-' . $module->getToken()] =
                    $moduleManager->displayModulePartial($module, $userEventInvitation->getModuleInvitationForModule($module));
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans('module.message.success.publish'));
                return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
            }
        }
    }

    /**
     * @Route("/remove/{token}", name="removeEventModule")
     * @ParamConverter("module", class="AppBundle:Event\Module")
     */
    public function removeEventModuleAction(Module $module, Request $request)
    {
        $userEventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($module->getEvent(), false, false, $this->getUser());
        $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
        if ($request->server->get('HTTP_REFERER') != $this->generateUrl('wizardNewEventStep2', array('token' => $module->getEvent()->getToken()), UrlGenerator::ABSOLUTE_URL) &&
            ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
        ) {
            // Vérification serveur de la validité de l'invitation
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                return $this->redirectToRoute('displayEvent', array('token' => $module->getEvent()->getToken()));
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                if ($this->isGranted(ModuleVoter::DELETE, array($module, $userModuleInvitation))) {
                    $moduleManager = $this->get("at.manager.module");
                    $moduleManager->setModule($module);
                    $moduleManager->removeModule();
                    $responseData[AppJsonResponse::DATA]['moduleToken'] = $module->getToken();
                    return new JsonResponse($responseData, Response::HTTP_OK);
                } else {
                    $responseData[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                    return new AppJsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
                }
            } else {
                $this->denyAccessUnlessGranted(ModuleVoter::DELETE, array($module, $userModuleInvitation));
                $moduleManager = $this->get("at.manager.module");
                $moduleManager->setModule($module);
                $moduleManager->removeModule();
                return $this->redirectToRoute('displayEvent', array('token' => $module->getEvent()->getToken()));
            }
        }
    }
}