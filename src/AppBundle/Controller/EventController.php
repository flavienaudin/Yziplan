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
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\FlashBagTypes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/{_locale}/evenement", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="createEvent")
     */
    public function createEventAction(Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        /** @var Event $currentEvent */
        $currentEvent = $eventManager->initializeEvent(true);
        if ($currentEvent != null) {
            $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY, $currentEvent->getCreator()->getToken());
            return $this->redirectToRoute("displayEvent", array('token' => $currentEvent->getToken(), 'tokenEdition' => $currentEvent->getTokenEdition()));
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.unauthorized_access"));
            return $this->redirect('home');
        }
    }

    /**
     * @Route("/{_locale}/evenement/{token}/{tokenEdition}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="displayEvent")
     */
    public function displayEventAction($token, $tokenEdition = null, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        if (($currentEvent = $eventManager->retrieveEvent($token)) != null) {
            /////////////////////////////////////
            // user EventInvitation management //
            /////////////////////////////////////
            $eventInvitationManager = $this->get("at.manager.event_invitation");
            $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($currentEvent, true, true, $this->getUser());
            if ($userEventInvitation == null) {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.error.message.unauthorized_access"));
                return $this->redirectToRoute("home");
            } else {
                $eventInvitationForm = $eventInvitationManager->createEventInvitationForm();
                $eventInvitationForm->handleRequest($request);
                if ($request->isXmlHttpRequest()) {
                    if ($eventInvitationForm->isSubmitted()) {
                        if ($eventInvitationForm->isValid()) {
                            $userEventInvitation = $eventInvitationManager->treatEventInvitationFormSubmission($eventInvitationForm);
                            // Update the form with the updated userEventInvitation
                            $eventInvitationForm = $eventInvitationManager->createEventInvitationForm();
                            //$data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data['htmlContent'] = $this->renderView("@App/Event/partials/eventInvitationUserMainDataCard.html.twig", array(
                                "userEventInvitation" => $userEventInvitation,
                                "userEventInvitationForm" => $eventInvitationForm->createView()
                            ));
                            return new JsonResponse($data, Response::HTTP_OK);
                        } else {
                            $data["formErrors"] = array();
                            foreach ($eventInvitationForm->getErrors(true) as $error) {
                                $data["formErrors"][$error->getOrigin()->getName()] = $error->getMessage();
                            }
                            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    }
                } else if ($eventInvitationForm->isValid()) {
                    $eventInvitationManager->treatEventInvitationFormSubmission($eventInvitationForm);
                    return $this->redirectToRoute('displayEvent', array(
                        'token' => $currentEvent->getToken(),
                        'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($currentEvent, $tokenEdition)) ? $currentEvent->getTokenEdition() : null)));
                }
            }

            ////////////////////////
            // Edition management //
            ////////////////////////
            $eventForm = null;
            if ($this->isGranted(EventVoter::EDIT, array($currentEvent, $tokenEdition))) {
                /** @var Form $eventForm */
                $eventForm = $eventManager->initEventForm();
                $eventForm->handleRequest($request);
                if ($request->isXmlHttpRequest()) {
                    if ($eventForm->isSubmitted()) {
                        if ($eventForm->isValid()) {
                            $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                            $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data['htmlContent'] = $this->renderView("@App/Event/partials/eventHeaderCard.html.twig", array(
                                    'event' => $currentEvent,
                                    'eventForm' => $eventForm->createView()
                                )
                            );
                            return new JsonResponse($data, Response::HTTP_OK);
                        } else {
                            $data["formErrors"] = array();
                            foreach ($eventForm->getErrors(true) as $error) {
                                $data["formErrors"][$error->getOrigin()->getName()] = $error->getMessage();
                            }
                            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    }
                } elseif ($eventForm->isValid()) {
                    $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                    return $this->redirectToRoute('displayEvent', array(
                        'token' => $currentEvent->getToken(),
                        'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($currentEvent, $tokenEdition)) ? $currentEvent->getTokenEdition() : null)));
                }
            }

            ////////////////////////
            // modules management //
            ////////////////////////
            $modules = $eventManager->getModulesToDisplay($userEventInvitation);
            $moduleManager = $this->get("at.manager.module");
            foreach ($modules as $moduleId => $moduleDescription) {
                if (key_exists('moduleForm', $moduleDescription) && $moduleDescription['moduleForm'] instanceof Form) {
                    /** @var Form $moduleForm */
                    $moduleForm = $moduleDescription['moduleForm'];
                    $moduleForm->handleRequest($request);
                    if ($request->isXmlHttpRequest()) {
                        if ($moduleForm->isSubmitted()) {
                            if ($moduleForm->isValid()) {
                                $currentModule = $moduleManager->treatUpdateFormModule($moduleForm);
                                $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                                $data['htmlContent'] = $moduleManager->displayModulePartial($currentModule, $userEventInvitation->getModuleInvitationForModule($moduleDescription['module']));
                                return new JsonResponse($data, Response::HTTP_OK);
                            } else {
                                $data["formErrors"] = array();
                                foreach ($moduleForm->getErrors(true) as $error) {
                                    $data["formErrors"][$error->getOrigin()->getName()] = $error->getMessage();
                                }
                                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                            }
                        }
                    } else {
                        if ($moduleForm->isValid()) {
                            $module = $moduleManager->treatUpdateFormModule($moduleForm);
                            return $this->redirect($this->generateUrl('displayEvent', array(
                                    'token' => $currentEvent->getToken(),
                                    'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($currentEvent, $tokenEdition)) ? $currentEvent->getTokenEdition() : null)
                                )) . '#module-' . $module->getToken());
                        }
                    }
                    $modules[$moduleId]['moduleForm'] = $moduleForm->createView();
                }

                //////////////////////
                // poll module case //
                //////////////////////
                if (array_key_exists('pollProposalAddForm', $moduleDescription) && $moduleDescription['pollProposalAddForm'] instanceof Form) {
                    /** @var FormInterface $pollProposalAddForm */
                    $pollProposalAddForm = $moduleDescription['pollProposalAddForm'];
                    $pollProposalAddForm->handleRequest($request);
                    if ($request->isXmlHttpRequest()) {
                        if ($pollProposalAddForm->isSubmitted()) {
                            if ($pollProposalAddForm->isValid()) {
                                $pollProposal = $moduleManager->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                                $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                                $data['htmlContent']['pollProposalRowDisplay'] = $moduleManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);
                                return new JsonResponse($data, Response::HTTP_OK);
                            } else {
                                $data["formErrors"] = array();
                                foreach ($pollProposalAddForm->getErrors(true) as $error) {
                                    $data["formErrors"][$error->getOrigin()->getName()] = $error->getMessage();
                                }
                                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                            }
                        }
                    } else {
                        if ($pollProposalAddForm->isValid()) {
                            $moduleManager->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                            return $this->redirect($this->generateUrl('displayEvent', array(
                                    'token' => $currentEvent->getToken(),
                                    'tokenEdition' => ($this->isGranted(EventVoter::EDIT, array($currentEvent, $tokenEdition)) ? $currentEvent->getTokenEdition() : null)
                                )) . '#module-' . $moduleDescription['module']->getToken());
                        }
                    }
                    $modules[$moduleId]['pollProposalAddForm'] = $pollProposalAddForm->createView();
                }
            }

            return $this->render('AppBundle:Event:event.html.twig', array(
                'event' => $currentEvent,
                'eventForm' => ($eventForm != null ? $eventForm->createView() : null),
                'modules' => $modules,
                'userEventInvitation' => $userEventInvitation,
                'userEventInvitationForm' => ($eventInvitationForm != null ? $eventInvitationForm->createView() : null)
            ));

        }
        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.unauthorized_access"));
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
}