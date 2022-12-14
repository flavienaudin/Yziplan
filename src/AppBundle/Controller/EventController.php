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
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\Response\AppJsonResponse;
use AppBundle\Utils\Response\FileInputJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class EventController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventController extends Controller
{

    const REDIRECTED_AFTER_EVENT_DUPLICATION = "redirected/eventDuplication";

    /**
     * @Route("/new", name="createEvent")
     */
    public function createEventAction(Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        /** @var Event $currentEvent */
        $currentEvent = $eventManager->initializeEvent();
        /** @var EventInvitation $currentEventInvitation Only one EventInvitation added when initialize an Event. */
        $currentEventInvitation = $currentEvent->getEventInvitations()->first();
        if ($currentEventInvitation != null) {
            $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $currentEvent->getToken(), $currentEventInvitation->getToken());
        }
        return $this->redirectToRoute('wizardNewEventStep1', array('token' => $currentEvent->getToken()));
    }

    /**
     * Action de duplication d'un ??v??nement "Template" : ?? partir du tokenDuplication, un nouvel ??v??nement est cr???? avec les m??mes informations de l'??v??nement,
     * les m??mes modules. Seule l'invitation de l'organisateur est cr??e.
     * @Route("/d/{tokenDuplication}", name="duplicateEvent")
     * @ParamConverter("event", class="AppBundle:Event\Event", options={"mapping": {"tokenDuplication": "tokenDuplication"}})
     */
    public function duplicateEventAction(Event $event, Request $request)
    {
        $eventManager = $this->get("at.manager.event");
        $duplicatedEvent = $eventManager->duplicateEvent(true, $event);
        if ($duplicatedEvent == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("duplication.message.error"));
            return $this->redirectToRoute('home');
        } else {
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
                    $userModuleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                    if (($pollModule = $userModuleInvitation->getModule()->getPollModule()) != null) {
                        /** @var PollProposal $pollProposal */
                        foreach ($pollModule->getPollProposals() as $pollProposal) {
                            $pollProposal->setCreator($userModuleInvitation);
                        }
                    }
                }
                $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $duplicatedEvent->getToken(), $userEventInvitation->getToken());
            }
            // TODO : ce n'est pas l'impl??mentation id??ale mais en attendant de r??fl??chir ?? une meilleure solution (get redirection in PROD env ?)
            $request->getSession()->set(self::REDIRECTED_AFTER_EVENT_DUPLICATION, $event->getTokenDuplication());
            $entityManager = $this->get('doctrine.orm.entity_manager');

            $entityManager->persist($duplicatedEvent);
            $entityManager->flush();

            return $this->redirectToRoute('displayEvent', array('token' => $duplicatedEvent->getToken()));
        }
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
            if ($currentEvent->getStatus() == EventStatus::DEPROGRAMMED) {
                return $this->render("@App/Event/event_cancelled.html.twig", array("event" => $currentEvent, "userEventInvitation" => $userEventInvitation));
            }

            /* TODO : invitation annul??e : rediriger vers une page "EventInvitation annul??e" */
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
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationForm' => $eventInvitationForm->createView()
                            ));
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
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } else if ($eventInvitationAnswerForm->isValid()) {
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
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($eventInvitationAnswerForm->isValid()) {
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
        /** @var FormInterface $eventForm */
        $eventForm = null;
        /** @var FormInterface $templateSettingsForm */
        $templateSettingsForm = null;
        /** @var FormInterface $recurrenceSettingsForm */
        $recurrenceSettingsForm = null;
        $sendMessageForm = null;
        if ($this->isGranted(EventVoter::EDIT, $userEventInvitation)) {
            $eventForm = $eventManager->initEventForm();
            $eventForm->handleRequest($request);
            if ($eventForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } elseif ($eventForm->isValid()) {
                        $eventManager->treatEventFormSubmission($eventForm, $userEventInvitation);
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");

                        // Creation du templateSettingsForm n??cessaire ?? l'affichage du template
                        $templateSettingsForm = $eventManager->createTemplateSettingsForm();

                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#event-header-card'] =
                            $this->renderView("@App/Event/partials/event_header_card.html.twig", array(
                                    'userEventInvitation' => $userEventInvitation,
                                    'eventForm' => $eventForm->createView(),
                                    'eventTemplateSettingsForm' => ($templateSettingsForm != null ? $templateSettingsForm->createView() : null),
                                    'eventRecurrenceSettingsForm' => ($recurrenceSettingsForm != null ? $recurrenceSettingsForm->createView() : null),
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
                } else {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($eventForm->isValid()) {
                        $currentEvent = $eventManager->treatEventFormSubmission($eventForm, $userEventInvitation);
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }

            $templateSettingsForm = $eventManager->createTemplateSettingsForm();
            $templateSettingsForm->handleRequest($request);
            if ($templateSettingsForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } elseif ($templateSettingsForm->isValid()) {
                        $currentEvent = $eventManager->treatTemplateSettingsForm($templateSettingsForm);
                        $templateSettingsForm = $eventManager->createTemplateSettingsForm();
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventTemplateSettings_formContainer'] =
                            $this->renderView("@App/Event/partials/event_duplication_template_settings_form.html.twig", array(
                                'event' => $currentEvent,
                                'eventTemplateSettingsForm' => $templateSettingsForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventEdit_form_container'] =
                            $this->renderView("@App/Event/partials/event_duplication_template_settings_form.html.twig", array(
                                'event' => $currentEvent,
                                'eventTemplateSettingsForm' => $templateSettingsForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($templateSettingsForm->isValid()) {
                        $currentEvent = $eventManager->treatTemplateSettingsForm($templateSettingsForm);
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }

            /** @var FormInterface $sendMessageForm */
            $sendMessageForm = $eventManager->createSendMessageForm();
            $sendMessageForm->handleRequest($request);
            if ($sendMessageForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } elseif ($sendMessageForm->isValid()) {
                        $failedRecipients = $eventManager->treatSendMessageFormSubmission($sendMessageForm, $userEventInvitation);
                        if ($failedRecipients === false) {
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.internal_server_error");
                            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                        } else {
                            if (count($failedRecipients) == 0) {
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("send_message.message.success");
                            } else {
                                $failedGuests = "";
                                /** @var EventInvitation $eventInvitation */
                                foreach ($failedRecipients as $eventInvitation) {
                                    if (!empty($failedGuests)) {
                                        $failedGuests .= ', ';
                                    }
                                    $failedGuests .= $eventInvitation->getDisplayableName();
                                }
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get('translator')->trans("send_message.message.warning", array('%failedRecipients%' => $failedGuests));
                            }

                            $sendMessageForm = $eventManager->createSendMessageForm();
                            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#sendMessageForm_container'] =
                                $this->renderView("@App/Event/partials/invitations/invitations_send_message_form.html.twig", array(
                                    'userEventInvitation' => $userEventInvitation,
                                    'sendMessageForm' => $sendMessageForm->createView()
                                ));
                            return new AppJsonResponse($data, Response::HTTP_OK);
                        }
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#sendMessageForm_container'] =
                            $this->renderView("@App/Event/partials/invitations/invitations_send_message_form.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'sendMessageForm' => $sendMessageForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($sendMessageForm->isValid()) {
                        $failedRecipients = $eventManager->treatSendMessageFormSubmission($sendMessageForm, $userEventInvitation);
                        if ($failedRecipients === false) {
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.internal_server_error");
                        } else {
                            if (count($failedRecipients) == 0) {
                                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("send_message.message.success"));
                            } else {
                                $failedGuests = "";
                                /** @var EventInvitation $eventInvitation */
                                foreach ($failedRecipients as $eventInvitation) {
                                    if (!empty($failedGuests)) {
                                        $failedGuests .= ', ';
                                    }
                                    $failedGuests .= $eventInvitation->getDisplayableName();
                                }
                                $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get('translator')->trans("send_message.message.warning", array('%failedRecipients%' => $failedGuests)));
                            }
                        }
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }
        }

        $eventInvitationsForm = null;
        ////////////////////////////
        // Invitations management //
        ////////////////////////////
        // L'utilisateur peut inviter si la configuration de l'??v??nement le permet ou si l'utilisateur est organisateur ou administrateur
        if ($this->isGranted(EventInvitationVoter::INVITE, $currentEvent) || $userEventInvitation->isCreator() || $userEventInvitation->isAdministrator()) {
            /** @var FormInterface $eventInvitationsForm */
            $eventInvitationsForm = $eventManager->createEventInvitationsForm();
            $eventInvitationsForm->handleRequest($request);
            if ($eventInvitationsForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    } else if ($eventInvitationsForm->isValid()) {
                        $resultInvitations = array();
                        $currentEvent = $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm, $resultInvitations);

                        if ((($nbFailed = count($resultInvitations['failed'])) + ($nbCreationError = count($resultInvitations['creationError']))) > 0) {
                            if ($nbFailed > 0) {
                                $emails = implode(", ", array_keys($resultInvitations['failed']));
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get("translator")->trans("invitations.message.mail_not_sent", ['%email_list%' => $emails]);
                            }
                            if ($nbCreationError > 0) {
                                $emails = implode(", ", array_values($resultInvitations['creationError']));
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] =
                                    $this->get("translator")->trans("invitations.message.invitation_not_created", ['%email_list%' => $emails]);
                            }
                        } else {
                            $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("invitations.message.success");
                        }

                        $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#invitations_forms_container'] =
                            $this->renderView("@App/Event/partials/invitations/invitations_new_form.html.twig", array(
                                'event' => $currentEvent,
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
                            $this->renderView("@App/Event/partials/invitations/invitations_new_form.html.twig", array(
                                'event' => $currentEvent,
                                'invitationsForm' => $eventInvitationsForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                        // V??rification serveur de la validit?? de l'invitation
                        $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    } elseif ($eventInvitationsForm->isValid()) {
                        $resultInvitations = array();
                        $eventManager->treatEventInvitationsFormSubmission($eventInvitationsForm, $resultInvitations);

                        if ((($nbFailed = count($resultInvitations['failed'])) + ($nbCreationError = count($resultInvitations['creationError']))) > 0) {
                            if ($nbFailed > 0) {
                                $emails = implode(", ", array_keys($resultInvitations['failed']));
                                $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get("translator")->trans("invitations.message.mail_not_sent", ['%email_list%' => $emails]));
                            }
                            if ($nbCreationError > 0) {
                                $emails = implode(", ", array_values($resultInvitations['creationError']));
                                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("invitations.message.invitation_not_created", ['%email_list%' => $emails]));
                            }
                        } else {
                            $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("invitations.message.success"));
                        }
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
            }
        }

        ////////////////////////
        // modules management //
        ////////////////////////
        $modules = $eventManager->getModulesToDisplay($userEventInvitation);
        $response = $eventManager->treatModulesToDisplay($currentEvent, $modules, $userEventInvitation, $request);
        if ($response instanceof Response) {
            return $response;
        }

        // Cas de duplication
        $redirectedAfterEventDuplication = false;
        if ($request->getSession()->has(self::REDIRECTED_AFTER_EVENT_DUPLICATION)) {
            $redirectedAfterEventDuplication = true;
            $request->getSession()->remove(self::REDIRECTED_AFTER_EVENT_DUPLICATION);
        }

        /** @var $notifications array Tableau de notifications {idx => Notification} */
        $notifications = $userEventInvitation->getSortedNotifications('Desc');

        return $this->render('AppBundle:Event:event.html.twig', array(
            'event' => $currentEvent,
            'eventForm' => ($eventForm != null ? $eventForm->createView() : null),
            'eventTemplateSettingsForm' => ($templateSettingsForm != null ? $templateSettingsForm->createView() : null),
            'eventRecurrenceSettingsForm' => ($recurrenceSettingsForm != null ? $recurrenceSettingsForm->createView() : null),
            'thread' => $thread, 'comments' => $comments,
            'sendMessageForm' => ($sendMessageForm != null ? $sendMessageForm->createView() : null),
            'invitationsForm' => ($eventInvitationsForm != null ? $eventInvitationsForm->createView() : null),
            'modules' => $modules,
            'userEventInvitation' => $userEventInvitation,
            'userEventInvitationForm' => $eventInvitationForm->createView(),
            'userEventInvitationAnswerForm' => $eventInvitationAnswerForm->createView(),
            'redirectedAfterEventDuplication' => $redirectedAfterEventDuplication,
            'notifications' => $notifications
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
            $event->setPictureFile($imageFile);
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
            if ($event->getPictureFilename() != null) {
                $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
                $data[AppJsonResponse::DATA]['picture_url'] = $helper->asset($event, 'pictureFile');
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
     * @Route("/set-event-parameter/{token}", name="setEventParameter")
     * @ParamConverter("event" , class="AppBundle:Event\Event")
     * @param Event $event The current event
     * @return AppJsonResponse|FileInputJsonResponse|RedirectResponse
     */
    public function setEventParameterAction(Event $event, Request $request)
    {
        $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        if (!$this->isGranted(EventVoter::EDIT, $eventInvitation)) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.unauthorized_access"));
                return $this->redirectToRoute("displayEvent", array("token" => $event->getToken()));
            }
        }
        $eventManager = $this->get("at.manager.event");
        if ($eventManager->setEventParameter($request->request->all(), $event)) {
            $parameter = $request->get('parameter');
            if ($request->isXmlHttpRequest()) {
                $data = array();
                if ($parameter === "guestsCanInvite") {
                    if ($event->isGuestsCanInvite()) {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans('event.form.guestsCanInvite.text.true');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_HTML]['#setGuestsCanInviteParameterLink'] =
                            '<i class="zmdi zmdi-check c-green"></i> ' . $this->get('translator')->trans("event.form.guestsCanInvite.text.true");
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans('event.form.guestsCanInvite.text.false');
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_HTML]['#setGuestsCanInviteParameterLink'] =
                            '<i class="zmdi zmdi-close c-red"></i> ' . $this->get('translator')->trans("event.form.guestsCanInvite.text.false");
                    }
                }
                if ($parameter === "invitationOnly") {
                    if ($event->isInvitationOnly()) {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans('event.form.invitationOnly.text.true');
                    } else {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans('event.form.invitationOnly.text.false');
                    }
                }
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans('event.success.message.edition'));
                return $this->redirectToRoute("displayEvent", array("token" => $event->getToken()));
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.edition");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.edition"));
                return $this->redirectToRoute("displayEvent", array("token" => $event->getToken()));
            }
        }
    }

    /**
     * @Route("/cancel/{token}", name="cancelEvent")
     * @ParamConverter("event" , class="AppBundle:Event\Event")
     * @param Event $event The current event
     * @return AppJsonResponse|FileInputJsonResponse|RedirectResponse
     */
    public function cancelEventAction(Event $event, Request $request)
    {
        $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        if (!$this->isGranted(EventVoter::CANCEL, $eventInvitation)) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.unauthorized_access"));
                return $this->redirectToRoute("displayEvent", array("tolen" => $event->getToken()));
            }
        }

        $eventManager = $this->get("at.manager.event");
        $requestData = $request->request->all();
        if ($eventManager->cancelEvent($requestData, $eventInvitation, $event)) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans('event.success.message.cancellation');
                $data[AppJsonResponse::REDIRECT] = $this->generateUrl("displayEvent", array("token" => $event->getToken()));
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                return $this->redirectToRoute("displayEvent", array("token" => $event->getToken()));
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.deletion");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.deletion"));
                return $this->redirectToRoute("displayEvent", array("token" => $event->getToken()));
            }
        }
    }

    /**
     * @Route("/status/validated/{token}", name="validateEvent")
     * @ParamConverter("event" , class="AppBundle:Event\Event")
     * @param Event $event The current event
     * @return AppJsonResponse|RedirectResponse
     */
    public function validateEventAction(Event $event, Request $request)
    {
        // TODO : v??rifier le fonctionnement de la m??thode avec l'activation de la fonctionnalit??
        $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($event, false, false, $this->getUser());
        if (!$this->isGranted(EventVoter::EDIT, $eventInvitation)) {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.unauthorized_access"));
                return $this->redirectToRoute("displayEvent", array("tolen" => $event->getToken()));
            }
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