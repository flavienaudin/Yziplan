<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Event\Event;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EventController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventController extends Controller
{

    /**
     * @Route("/show-test", name="eventTest")
     * @deprecated
     */
    public function eventTestAction(Request $request)
    {
        return $this->render('AppBundle:Event/proto:test-event.html.twig');
    }


    /**
     * @Route("/new", name="createEvent")
     */
    public function createEventAction(Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        /** @var Event $currentEvent */
        $currentEvent = $eventManager->initializeEvent(true);
        // Only one EventInvitation added when initialize an Event.
        $currentEventInvitation = $currentEvent->getEventInvitations()->first();
        if ($currentEventInvitation != null) {
            $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY, $currentEventInvitation->getToken());
        }
        return $this->redirectToRoute("displayEvent", array('token' => $currentEvent->getToken()));
    }

    /**
     * @Route("/show/{token}", name="displayEvent")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function displayEventAction(Event $currentEvent, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        $eventManager->setEvent($currentEvent);

        /////////////////////////////////////
        // User EventInvitation management //
        /////////////////////////////////////
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($currentEvent, true, true, $this->getUser());
        if ($userEventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
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
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#user-event-invitation-card'] =
                            $this->renderView("@App/Event/partials/eventInvitationUserMainDataCard.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationForm' => $eventInvitationForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitationProfile_formContainer'] =
                            $this->renderView('@App/Event/partials/eventInvitation_profile_form.html.twig', array(
                                'userEventInvitationForm' => $eventInvitationForm->createView(),
                                'userEventInvitation' => $userEventInvitation,));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } else if ($eventInvitationForm->isValid()) {
                $eventInvitationManager->treatEventInvitationFormSubmission($eventInvitationForm);
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
            }
        }

        ////////////////////////
        // Edition management //
        ////////////////////////
        $eventForm = null;
        $eventInvitationsForm = null;
        if ($this->isGranted(EventVoter::EDIT, $userEventInvitation)) {
            /** @var Form $eventForm */
            $eventForm = $eventManager->initEventForm();
            $eventForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($eventForm->isSubmitted()) {
                    if ($eventForm->isValid()) {
                        $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                        $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                        $data['htmlContent'] = $this->renderView("@App/Event/partials/eventHeaderCard.html.twig", array(
                                'event' => $currentEvent,
                                'userEventInvitation' => $userEventInvitation,
                                'eventForm' => $eventForm->createView(),
                                'invitationsForm' => $eventInvitationsForm->createView()
                            )
                        );
                        return new JsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data["formErrors"] = array();
                        foreach ($eventForm->getErrors(true) as $error) {
                            $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                        }
                        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } elseif ($eventForm->isValid()) {
                $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
            }
        }

        ////////////////////////////
        // Invitations management //
        ////////////////////////////
        if ($this->isGranted(EventInvitationVoter::INVITE, $currentEvent) || $this->isGranted(EventInvitationVoter::EDIT, $userEventInvitation)) {
            /** @var FormInterface $eventInvitationsForm */
            $eventInvitationsForm = $eventManager->createEventInvitationsForm();
            $eventInvitationsForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($eventInvitationsForm->isSubmitted()) {
                    if ($eventInvitationsForm->isValid()) {
                        $currentEvent = $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm);
                        $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                        $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $data['htmlContent'] = $this->renderView("@App/Event/partials/eventInvitations.html.twig", array(
                            'event' => $currentEvent,
                            'userEventInvitation' => $userEventInvitation,
                            'eventInvitations' => $currentEvent->getEventInvitations(),
                            'invitationsForm' => $eventInvitationsForm->createView(),
                            'editEventMode' => true

                        ));
                        return new JsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data["formErrors"] = array();
                        foreach ($eventInvitationsForm->getErrors(true) as $error) {
                            $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                            $data["messages"][FlashBagTypes::WARNING_TYPE][] = $this->get("translator")->trans($error->getMessage());
                        }
                        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } elseif ($eventInvitationsForm->isValid()) {
                $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm);
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
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
                                $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                            }
                            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    }
                } else {
                    if ($moduleForm->isValid()) {
                        $module = $moduleManager->treatUpdateFormModule($moduleForm);
                        return $this->redirect($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $module->getToken());
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
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data['htmlContent']['pollProposalRowDisplay'] = $moduleManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            $data["formErrors"] = array();
                            foreach ($pollProposalAddForm->getErrors(true) as $error) {
                                $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                            }
                            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    }
                } else {
                    if ($pollProposalAddForm->isValid()) {
                        $moduleManager->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                        return $this->redirect($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $moduleDescription['module']->getToken());
                    }
                }
                $modules[$moduleId]['pollProposalAddForm'] = $pollProposalAddForm->createView();
            }
        }

        return $this->render('AppBundle:Event:event.html.twig', array(
            'event' => $currentEvent,
            'eventForm' => ($eventForm != null ? $eventForm->createView() : null),
            'invitationsForm' => ($eventInvitationsForm != null ? $eventInvitationsForm->createView() : null),
            'modules' => $modules,
            'userEventInvitation' => $userEventInvitation,
            'userEventInvitationForm' => $eventInvitationForm->createView()
        ));
    }
}