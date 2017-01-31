<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use AppBundle\Utils\Response\FileInputJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class EventController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventController extends Controller
{
    const WIZARD_NEW_EVENT_STEP_MAIN = "main";
    const WIZARD_NEW_EVENT_STEP_PROFILE = "profile";
    const WIZARD_NEW_EVENT_STEP_INVITATIONS = "invitations";
    const WIZARD_NEW_EVENT_STEP_ADD_MODULE = "addmodule";

    /**
     * @Route("/new", name="createEvent")
     */
    public function createEventAction(Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        /** @var Event $currentEvent */
        $currentEvent = $eventManager->initializeEvent(true);
        /** @var EventInvitation $currentEventInvitation Only one EventInvitation added when initialize an Event. */
        $currentEventInvitation = $currentEvent->getEventInvitations()->first();
        if ($currentEventInvitation != null) {
            $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $currentEvent->getToken(), $currentEventInvitation->getToken());
        }

        return $this->redirectToRoute('wizardNewEvent', array('token' => $currentEvent->getToken(), 'stepIndex' => self::WIZARD_NEW_EVENT_STEP_MAIN));
    }

    /**
     * @Route("/wizard/{token}/{stepIndex}", name="wizardNewEvent")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function wizardNewEventAction(Event $currentEvent, $stepIndex = self::WIZARD_NEW_EVENT_STEP_MAIN, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        $eventManager->setEvent($currentEvent);

        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($currentEvent, false, false, $this->getUser());
        if ($userEventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
            return $this->redirectToRoute("home");
        } else {
            $this->denyAccessUnlessGranted(EventVoter::EDIT, $userEventInvitation);
            if ($stepIndex == self::WIZARD_NEW_EVENT_STEP_MAIN) {
                // Event Form :
                /** @var Form $eventForm */
                $eventForm = $eventManager->initEventForm();
                $eventForm->handleRequest($request);
                if ($eventForm->isSubmitted()) {
                    if ($eventForm->isValid()) {
                        $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                        return $this->redirectToRoute('wizardNewEvent', array('token' => $currentEvent->getToken(), 'stepIndex' => self::WIZARD_NEW_EVENT_STEP_PROFILE));
                    }
                }

                return $this->render("@App/Event/wizard/step_event_main.html.twig", array(
                    'event' => $currentEvent,
                    'userEventInvitation' => $userEventInvitation,
                    'eventForm' => $eventForm->createView()
                ));
            } elseif ($stepIndex == self::WIZARD_NEW_EVENT_STEP_PROFILE) {
                // Profile Form :
                /** @var Form $eventForm */
                $eventProfileForm = $eventInvitationManager->createEventInvitationForm();
                $eventProfileForm->handleRequest($request);
                if ($eventProfileForm->isSubmitted()) {
                    if ($eventProfileForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationFormSubmission($eventProfileForm);
                        return $this->redirectToRoute('wizardNewEvent', array('token' => $currentEvent->getToken(), 'stepIndex' => self::WIZARD_NEW_EVENT_STEP_INVITATIONS));
                    }
                }
                return $this->render("@App/Event/wizard/step_event_profile.html.twig", array(
                    'event' => $currentEvent,
                    'userEventInvitation' => $userEventInvitation,
                    'userEventInvitationForm' => $eventProfileForm->createView()
                ));
            } elseif ($stepIndex == self::WIZARD_NEW_EVENT_STEP_INVITATIONS) {
                // Invitations Form :
                $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                $eventInvitationsForm->handleRequest($request);
                if ($eventInvitationsForm->isSubmitted()) {
                    if ($eventInvitationsForm->isValid()) {
                        $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm, $request->get('sendInvitations'));
                        return $this->redirectToRoute('wizardNewEvent', array('token' => $currentEvent->getToken(), 'stepIndex' => self::WIZARD_NEW_EVENT_STEP_ADD_MODULE));
                    }
                }
                return $this->render("@App/Event/wizard/step_event_invitations.html.twig", array(
                    'event' => $currentEvent,
                    'userEventInvitation' => $userEventInvitation,
                    'invitationsForm' => $eventInvitationsForm->createView()
                ));
            } elseif ($stepIndex == self::WIZARD_NEW_EVENT_STEP_ADD_MODULE) {
                return $this->render("@App/Event/wizard/step_event_addModule.html.twig", array('event' => $currentEvent));
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('event.wizard.error.message.wrong_step'));
                return $this->redirectToRoute('home');
            }
        }
    }

    /**
     * @Route("/duplicate/{token}", name="duplicateEvent")
     * @ParamConverter("event", class="AppBundle:Event\Event")
     */
    public function duplicateEvent(Event $event, Request $request)
    {
        $eventManager = $this->get("at.manager.event");
        $duplicatedEvent = $eventManager->duplicateEvent(true, $event);

        $user = $this->getUser();
        $userApplicationUser = null;
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED) && $user instanceof AccountUser) {
            $userApplicationUser = $user->getApplicationUser();
        }

        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->createCreatorEventInvitation($duplicatedEvent, $userApplicationUser);
        if ($userEventInvitation != null) {
            // The creator is designated as creator of all modules and all pollProposals
            /** @var ModuleInvitation $userModuleInvitation */
            foreach ($userEventInvitation->getModuleInvitations() as $userModuleInvitation) {
                $userModuleInvitation->setCreator(true);
                if (($pollModule = $userModuleInvitation->getModule()->getPollModule()) != null) {
                    /** @var PollProposal $pollProposal */
                    foreach ($pollModule->getPollProposals() as $pollProposal) {
                        $pollProposal->setCreator($userModuleInvitation);
                    }
                }
            }
            $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $duplicatedEvent->getToken(), $userEventInvitation->getToken());
        }

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->persist($duplicatedEvent);
        $entityManager->flush();

        return $this->redirectToRoute('displayEvent', array('token' => $duplicatedEvent->getToken()));
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
            /* TODO : invitation annulée : désactiver les formulaires */
            if ($userEventInvitation->getStatus() == EventInvitationStatus::CANCELLED) {
                $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get('translator')->trans('eventInvitation.message.warning.invitation_cancelled'));
            }

            $eventInvitationForm = $eventInvitationManager->createEventInvitationForm();
            $eventInvitationForm->handleRequest($request);
            if ($eventInvitationForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($eventInvitationForm->isValid()) {
                        $userEventInvitation = $eventInvitationManager->treatEventInvitationFormSubmission($eventInvitationForm);
                        // Update the form with the updated userEventInvitation
                        $eventInvitationForm = $eventInvitationManager->createEventInvitationForm();
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitationProfile_formContainer'] =
                            $this->renderView("@App/Event/partials/profile/eventInvitation_profile_form.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationForm' => $eventInvitationForm->createView()
                            ));
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#guests_list'] =
                            $this->renderView("@App/Event/partials/guests_list/guestsList_card_body.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'event' => $currentEvent
                            ));
                        $data[AppJsonResponse::DATA]['userDisplayableName'] = $userEventInvitation->getDisplayableName(false);
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitationProfile_formContainer'] =
                            $this->renderView('@App/Event/partials/profile/eventInvitation_profile_form.html.twig', array(
                                'userEventInvitationForm' => $eventInvitationForm->createView(),
                                'userEventInvitation' => $userEventInvitation,));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($eventInvitationForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationFormSubmission($eventInvitationForm);
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }

            $eventInvitationAnswerForm = $eventInvitationManager->createEventInvitationAnswerForm();
            $eventInvitationAnswerForm->handleRequest($request);
            if ($eventInvitationAnswerForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($eventInvitationAnswerForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationAnswerFormSubmission($eventInvitationAnswerForm);
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('global.success.data_saved');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#guests_list'] =
                            $this->renderView("@App/Event/partials/guests_list/guestsList_card_body.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'event' => $currentEvent
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitation-answer-panel'] =
                            $this->renderView('@App/Event/partials/profile/eventInvitation_profile_answer_form.html.twig', array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationAnswerForm' => $eventInvitationAnswerForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($eventInvitationAnswerForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationAnswerFormSubmission($eventInvitationAnswerForm);
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }
        }

        ////////////////////////
        // Comment Management //
        ////////////////////////
        $thread = $currentEvent->getCommentThread();
        $discussionManager = $this->container->get('at.manager.discussion');
        if (null == $thread) {
            $thread = $discussionManager->createCommentableThread($currentEvent);
        }
        $comments = $discussionManager->getCommentsThread($thread);

        ////////////////////////
        // Edition management //
        ////////////////////////
        $eventForm = null;
        if ($this->isGranted(EventVoter::EDIT, $userEventInvitation)) {
            /** @var Form $eventForm */
            $eventForm = $eventManager->initEventForm();
            $eventForm->handleRequest($request);
            if ($eventForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($eventForm->isValid()) {
                        $eventManager->treatEventFormSubmission($eventForm);
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                            $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                                    'userEventInvitation' => $userEventInvitation,
                                    'eventForm' => $eventForm->createView(),
                                    'thread' => $thread, 'comments' => $comments,
                                )
                            );
                        $eventForm = $eventManager->initEventForm();
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventEdit_form_container'] =
                            $this->renderView("@App/Event/partials/event_edit_form.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'eventForm' => $eventForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventEdit_form_container'] =
                            $this->renderView("@App/Event/partials/event_edit_form.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'eventForm' => $eventForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } elseif ($eventForm->isValid()) {
                    $currentEvent = $eventManager->treatEventFormSubmission($eventForm);
                    return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                }
            }
        }

        // TODO Revoir le système d'invitation
        $eventInvitationsForm = null;
        ////////////////////////////
        // Invitations management //
        ////////////////////////////
        // L'utilisateur peut inviter si la configuration de l'événement le permet ou si l'utilisateur est organisateur ou administrateur
        if ($this->isGranted(EventInvitationVoter::INVITE, $currentEvent) || $userEventInvitation->isCreator() || $userEventInvitation->isAdministrator()) {
            /** @var FormInterface $eventInvitationsForm */
            $eventInvitationsForm = $eventManager->createEventInvitationsForm();
            $eventInvitationsForm->handleRequest($request);
            if ($eventInvitationsForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION) {
                        // Vérification serveur de la validité de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } else if ($eventInvitationsForm->isValid()) {
                        $currentEvent = $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm);
                        $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("invitations.message.success");
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#invitations_forms_container'] =
                            $this->renderView("@App/Event/partials/invitations/invitations_new_forms.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'invitationsForm' => $eventInvitationsForm->createView()

                            ));
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#guests_list'] =
                            $this->renderView("@App/Event/partials/guests_list/guestsList_card_body.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'event' => $currentEvent
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#invitations_forms_container'] =
                            $this->renderView("@App/Event/partials/invitations/invitations_new_forms.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'invitationsForm' => $eventInvitationsForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION) {
                        // Vérification serveur de la validité de l'invitation
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE] =
                            $this->get('translator')->trans("eventInvitation.profile.card.guestname_required_alert.error_message.unauthorized_action");;
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($eventInvitationsForm->isValid()) {
                        $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm);
                        $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("global.success.data_saved"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
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
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_HTML]['.module-' . $currentModule->getToken() . '-description'] = $currentModule->getDescription();
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-header-' . $currentModule->getToken()] =
                                $this->renderView("@App/Event/module/displayModule_header.html.twig", array(
                                    'module' => $moduleDescription['module'],
                                    'moduleForm' => $moduleForm->createView(),
                                    'userModuleInvitation' => $userEventInvitation->getModuleInvitationForModule($moduleDescription['module'])
                                ));
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#module-information-' . $currentModule->getToken()] =
                                $this->renderView("@App/Event/module/displayModule_informations.html.twig", array('module' => $moduleDescription['module']));
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        } else {
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#moduleEdit_form_' . $moduleDescription['module']->getToken()] =
                                $this->renderView('@App/Event/module/displayModule_form.html.twig', array(
                                    'module' => $moduleDescription['module'],
                                    'moduleForm' => $moduleForm->createView(),
                                    'userModuleInvitation' => $userEventInvitation->getModuleInvitationForModule($moduleDescription['module'])
                                ));
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
                if ($pollProposalAddForm->isSubmitted()) {
                    if ($request->isXmlHttpRequest()) {
                        if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION) {
                            // Vérification serveur de la validité de l'invitation
                            $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        } else {
                            if ($pollProposalAddForm->isValid()) {
                                $pollProposalManager = $this->get('at.manager.pollproposal');
                                $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                                $data[AppJsonResponse::DATA] = $pollProposalManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);
                                return new AppJsonResponse($data, Response::HTTP_OK);
                            } else {
                                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#add_pp_fm_' . $moduleDescription['module']->getToken() . '_formContainer'] =
                                    $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                        'userModuleInvitation' => $userEventInvitation->getModuleInvitationForModule($moduleDescription['module']),
                                        'pollProposalForm' => $pollProposalAddForm->createView(),
                                        'pp_form_modal_prefix' => "add_pp_fm_" . $moduleDescription['module']->getToken(),
                                        'edition' => false
                                    ));
                                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                            }
                        }
                    } else {
                        if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION) {
                            // Vérification serveur de la validité de l'invitation
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE] =
                                $this->get('translator')->trans("eventInvitation.profile.card.guestname_required_alert.error_message.unauthorized_action");;
                            return $this->redirect($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $moduleDescription['module']->getToken());
                        } else if ($pollProposalAddForm->isValid()) {
                            $this->get('at.manager.pollproposal')->treatPollProposalForm($pollProposalAddForm, $moduleDescription['module']);
                            return $this->redirect($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $moduleDescription['module']->getToken());
                        }
                    }
                }
                $modules[$moduleId]['pollProposalAddForm'] = $pollProposalAddForm->createView();
            }
        }

        return $this->render('AppBundle:Event:event.html.twig', array(
            'event' => $currentEvent,
            'eventForm' => ($eventForm != null ? $eventForm->createView() : null),
            'thread' => $thread, 'comments' => $comments,
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
            $pictureFocusX = $request->get('focusX');
            if ($pictureFocusX == null) {
                $pictureFocusX = 0;
            } elseif ($pictureFocusX < -1) {
                $pictureFocusX = -1;
            } elseif ($pictureFocusX > 1) {
                $pictureFocusX = 1;
            }

            $pictureFocusY = $request->get('focusY');
            if ($pictureFocusY == null) {
                $pictureFocusY = 0;
            } elseif ($pictureFocusY < -1) {
                $pictureFocusY = -1;
            } elseif ($pictureFocusY > 1) {
                $pictureFocusY = 1;
            }

            /** @var EventManager $eventManager */
            $eventManager = $this->get("at.manager.event");
            $eventManager->setEvent($event);
            if ($event->getPicture() != null) {
                $this->get('at.uploader.event_picture')->delete($event->getPicture());
            }
            $event->setPicture($imageFile);
            if ($imageFile != null) {
                $imageSizes = getimagesize($imageFile);
                $event->setPictureWidth($imageSizes[0]);
                $event->setPictureHeight($imageSizes[1]);
                $event->setPictureFocusX($pictureFocusX);
                $event->setPictureFocusY($pictureFocusY);
            } else {
                $event->setPictureWidth(null);
                $event->setPictureHeight(null);
                $event->setPictureFocusX(null);
                $event->setPictureFocusY(null);
            }
            $eventManager->persistEvent();
            $data = array();
            if ($event->getPicture() != null) {
                $avatarDirURL = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $this->get('at.uploader.event_picture')->getWebRelativeTargetDir();
                $data[AppJsonResponse::DATA]['picture_url'] = $avatarDirURL . '/' . $event->getPicture();
                $data[AppJsonResponse::DATA]['picture_focus_x'] = $event->getPictureFocusX();
                $data[AppJsonResponse::DATA]['picture_focus_y'] = $event->getPictureFocusY();
                $data[AppJsonResponse::DATA]['picture_width'] = $event->getPictureWidth();
                $data[AppJsonResponse::DATA]['picture_height'] = $event->getPictureHeight();
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

            $thread = $event->getCommentThread();
            if ($thread != null) {
                $discussionManager = $this->container->get('at.manager.discussion');
                $comments = $discussionManager->getCommentsThread($thread);
            } else {
                $comments = [];
            }

            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                        'userEventInvitation' => $eventInvitation,
                        'eventForm' => $eventForm->createView(),
                        'thread' => $thread, 'comments' => $comments
                    )
                );
            return new AppJsonResponse($data, Response::HTTP_OK);
        } else {
            return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
        }
    }
}