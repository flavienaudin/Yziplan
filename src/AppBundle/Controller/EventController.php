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
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use AppBundle\Utils\Response\FileInputJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function eventTestAction()
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
                            $this->renderView("@App/Event/partials/eventInvitation_profile_card.html.twig", array(
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


            $eventInvitationAnswerForm = $eventInvitationManager->createEventInvitationAnswerForm();
            $eventInvitationAnswerForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($eventInvitationAnswerForm->isSubmitted()) {
                    if ($eventInvitationAnswerForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationAnswerFormSubmission($eventInvitationAnswerForm);
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('global.success.data_saved');
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitation-answer-card'] =
                            $this->renderView('@App/Event/partials/eventInvitation_answerCard.html.twig', array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationAnswerForm' => $eventInvitationAnswerForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } else if ($eventInvitationAnswerForm->isValid()) {
                $eventInvitationManager->treatEventInvitationAnswerFormSubmission($eventInvitationAnswerForm);
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
                        $eventManager->treatEventFormSubmission($eventForm);
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                            $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                                    'userEventInvitation' => $userEventInvitation,
                                    'eventForm' => $eventForm->createView()
                                )
                            );
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                            $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                                    'userEventInvitation' => $userEventInvitation,
                                    'eventForm' => $eventForm->createView(),
                                )
                            );
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } elseif ($eventForm->isValid()) {
                $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
            }
        }

        // TODO Revoir le système d'invitation
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
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitations-main-div'] =
                            $this->renderView("@App/Event/partials/eventInvitations_card.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'eventInvitations' => $currentEvent->getEventInvitations(),
                                'invitationsForm' => $eventInvitationsForm->createView()

                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitations-main-div'] =
                            $this->renderView("@App/Event/partials/eventInvitations_card.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'eventInvitations' => $currentEvent->getEventInvitations(),
                                'invitationsForm' => $eventInvitationsForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            } elseif ($eventInvitationsForm->isValid()) {
                $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm);
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("global.success.data_saved"));
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
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-' . $currentModule->getToken()] =
                                $moduleManager->displayModulePartial($currentModule, $userEventInvitation->getModuleInvitationForModule($moduleDescription['module']));
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            // TODO vérifier que ca marche
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-' . $moduleDescription['module']->getToken()] =
                                $moduleManager->displayModulePartial($moduleDescription['module'], $userEventInvitation->getModuleInvitationForModule($moduleDescription['module']));
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
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
                            $pollProposalManager = $this->get('at.manager.pollproposal');
                            $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]['#pp-row-group-' . $moduleDescription['module']->getToken()] =
                                $pollProposalManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            // TODO vérifier que ca marche
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#add_pp_fm_' . $moduleDescription['module']->getToken() . '_formContainer'] =
                                $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                    'userModuleInvitation' => $userEventInvitation->getModuleInvitationForModule($moduleDescription['module']),
                                    'pollProposalForm' => $pollProposalAddForm,
                                    'pp_form_modal_prefix' => "add_pp_fm_" . $moduleDescription['module']->getToken(),
                                    'edition' => false
                                ));
                            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                        }
                    }
                } else {
                    if ($pollProposalAddForm->isValid()) {
                        $this->get('at.manager.pollproposal')->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
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
            'userEventInvitationForm' => $eventInvitationForm->createView(),
            'userEventInvitationAnswerForm' => $eventInvitationAnswerForm->createView()
        ));
    }


    /**
     * @Route("/picture/update/{token}", name="updateEventPicture")
     * @ParamConverter("event" , class="AppBundle:Event\Event")
     * @param Event $event The current event
     * @return AppJsonResponse|FileInputJsonResponse|RedirectResponse
     */
    public function updateEventPictureAction(Event $event, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
            if (!$this->isGranted(EventVoter::EDIT, $eventInvitation)) {
                $data[FileInputJsonResponse::ERROR] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new FileInputJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            }

            $imageFile = $request->files->get('eventPictureInput');
            /** @var EventManager $eventManager */
            $eventManager = $this->get("at.manager.event");
            $eventManager->setEvent($event);
            if ($event->getPicture() != null) {
                $this->get('at.uploader.event_picture')->delete($event->getPicture());
            }
            $event->setPicture($imageFile);
            $eventManager->persistEvent();
            $data = array();
            if ($event->getPicture() != null) {
                $avatarDirURL = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $this->get('at.uploader.event_picture')->getWebRelativeTargetDir();
                $data[AppJsonResponse::DATA] = $avatarDirURL . '/' . $event->getPicture();
            }
            return new AppJsonResponse($data, Response::HTTP_OK);

        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }


    /**
     * @Route("/status/validated/{token}", name="validateEvent")
     * @ParamConverter("event" , class="AppBundle:Event\Event")
     * @param Event $event The current event
     * @return AppJsonResponse|FileInputJsonResponse|RedirectResponse
     */
    public function validateEventAction(Event $event, Request $request)
    {
        $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        if (!$this->isGranted(EventVoter::EDIT, $eventInvitation)) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        $eventManager = $this->get("at.manager.event");
        $event->setStatus(EventStatus::VALIDATED);
        $eventManager->setEvent($event)->persistEvent();

        if ($request->isXmlHttpRequest()) {
            /** @var Form $eventForm */
            $eventForm = $eventManager->initEventForm();
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                        'userEventInvitation' => $eventInvitation,
                        'eventForm' => $eventForm->createView()
                    )
                );
            return new AppJsonResponse($data, Response::HTTP_OK);
        } else {
            return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
        }
    }
}