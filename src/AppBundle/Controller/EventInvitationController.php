<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/07/2016
 * Time: 16:19
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class EventInvitationController
 * @Route("/{_locale}/event-invitation", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventInvitationController extends Controller
{
    /**
     * Action pour afficher sur un événement à partir d'un token eventInvitation qui est alors enregistré en session avant la redirection vers la page d'affichage de l'événement
     *
     * @Route("/show/{token}", name="displayEventInvitation" )
     * @ParamConverter("eventInvitation", class="AppBundle:Event\EventInvitation")
     */
    public function displayEventInvitationAction(EventInvitation $eventInvitation = null, Request $request)
    {
        if ($eventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
            return $this->redirectToRoute("home");
        }
        $this->denyAccessUnlessGranted(EventInvitationVoter::EDIT, $eventInvitation);

        if ($eventInvitation->getApplicationUser() == null && $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            // Si l'invitation n'est pas affectée à un ApplicationUser et l'utilisateur est connecté
            // Alors on essaye d'affecter l'invitation à l'utilisateur si celui-ci n'a pas déjà une invitation pour cet événement
            /** @var AccountUser $user */
            $user = $this->getUser();
            $eventInvitationManager = $this->get('at.manager.event_invitation');
            $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($eventInvitation->getEvent(), false, false, $user);
            if ($userEventInvitation == null) {
                $eventInvitation->setApplicationUser($user->getApplicationUser());
                $eventInvitationManager
                    ->setEventInvitation($eventInvitation)
                    ->persistEventInvitation();
            } else {
                $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get('translator')->trans("eventInvitation.message.warning.another_invitation_already_exists_for_user"));
                $eventInvitation = $userEventInvitation;
            }
        }

        $request->getSession()->set(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $eventInvitation->getEvent()->getToken(), $eventInvitation->getToken());
        return $this->redirectToRoute("displayEvent", array('token' => $eventInvitation->getEvent()->getToken()));
    }

    /**
     * @Route("/disconnect/{eventToken}", name="disconnectEventInvitation" )
     */
    public function disconnectEventInvitationAction($eventToken, Request $request)
    {
        if ($request->hasSession()) {
            $request->getSession()->remove(EventInvitationManager::TOKEN_SESSION_KEY . '/' . $eventToken);
        }
        return $this->redirectToRoute("displayEvent", array('token' => $eventToken));
    }

    /**
     * TODO Not used yet
     * @ Route("/send/{token}", name="sendEventInvitations" )
     * @ ParamConverter("event", class="AppBundle:Event\Event")
     */
    /*public function sendEventInvitationAction(Event $event, Request $request)
    {
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($event, false, false, $this->getUser());
        if ($userEventInvitation == null || !$userEventInvitation->isOrganizer()) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
            return $this->redirectToRoute("home");
        } else {
            if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                // Vérification serveur de la validité de l'invitation
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("eventInvitation.profile.card.guestname_required_alert.error_message.unauthorized_action"));
                    return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
                }
            } else {
                $failedRecipients = array();
                $eventInvitationManager->sendInvitations($event->getEventInvitationEmailNotSent(), $failedRecipients);

                if (count($failedRecipients) > 0) {
                    $emails = "";
                    foreach (array_values($failedRecipients) as $email) {
                        if (!empty($email)) {
                            $emails .= ", ";
                        }
                        $emails .= $email;
                    }
                    if ($request->isXmlHttpRequest()) {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get('translator')->trans("invitations.message.error", ["%email_list%" => $emails]);
                    } else {
                        $this->addFlash(FlashBagTypes::WARNING_TYPE, $this->get('translator')->trans("invitations.message.error", ["%email_list%" => $emails]));
                    }
                } else {
                    if ($request->isXmlHttpRequest()) {
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("invitations.message.success");
                    } else {
                        $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get('translator')->trans("invitations.message.success"));
                    }
                }

                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#guests_list'] =
                        $this->renderView("@App/Event/partials/guests_list/guestsList_card_body.html.twig", array(
                            'userEventInvitation' => $userEventInvitation,
                            'event' => $event
                        ));
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    return $this->redirectToRoute('displayEvent', array('token' => $event->getToken()));
                }
            }
        }
    }*/


    /**
     * @Route("/modify-answer/{eventInvitTokenToModifyAnswer}/{answerValue}", name="modifyGuestEventInvitation" )
     * @ParamConverter("eventInvitTokenToModifyAnswer", class="AppBundle:Event\EventInvitation", options={"mapping": {"eventInvitTokenToModifyAnswer":"token"}})
     */
    public function modifyGuestEventInvitationAnswerAction(EventInvitation $eventInvitTokenToModifyAnswer, $answerValue, Request $request)
    {
        $userEventInvitation = null;
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        if ($eventInvitTokenToModifyAnswer != null) {
            // Get the UserInvitation
            $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($eventInvitTokenToModifyAnswer->getEvent(), false, false, $this->getUser());
        }

        // Only creator/admnsitrators can cancel an EventInvitation
        if ($this->isGranted(EventInvitationVoter::MODIFY_ANSWER, [$userEventInvitation, $eventInvitTokenToModifyAnswer])) {
            if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                // Vérification serveur de la validité de l'invitation
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                    return $this->redirectToRoute('displayEvent', array('token' => $userEventInvitation->getEvent()->getToken()));
                }
            } elseif ($eventInvitationManager->modifyAnswerEventInvitation($eventInvitTokenToModifyAnswer, $answerValue)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("invitations.display.modify_answer.message.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("invitations.display.modify_answer.message.success"));
                    return $this->redirectToRoute("displayEvent", array("token" => $eventInvitTokenToModifyAnswer->getEvent()->getToken()));
                }
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_modify_answer");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_modify_answer"));
            if ($eventInvitTokenToModifyAnswer != null) {
                return $this->redirectToRoute("displayEvent", array("token" => $eventInvitTokenToModifyAnswer->getEvent()->getToken()));
            } else {
                return $this->redirectToRoute("home");
            }
        }
    }

    /**
     * @Route("/cancel/{eventInvitationTokenToCancel}", name="cancelEventInvitation" )
     * @ParamConverter("eventInvitationToCancel", class="AppBundle:Event\EventInvitation", options={"mapping": {"eventInvitationTokenToCancel":"token"}})
     */
    public function cancelEventInvitationAction(EventInvitation $eventInvitationToCancel, Request $request)
    {
        $userEventInvitation = null;
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        if ($eventInvitationToCancel != null) {
            // Get the UserInvitation
            $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($eventInvitationToCancel->getEvent(), false, false, $this->getUser());
        }

        // Only creator/admnsitrators can cancel an EventInvitation
        if ($this->isGranted(EventInvitationVoter::CANCEL, [$userEventInvitation, $eventInvitationToCancel])) {
            if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                // Vérification serveur de la validité de l'invitation
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("event.error.message.valide_guestname_required"));
                    return $this->redirectToRoute('displayEvent', array('token' => $userEventInvitation->getEvent()->getToken()));
                }
            } elseif ($eventInvitationManager->cancelEventInvitation($eventInvitationToCancel)) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("invitations.display.cancel.message.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("invitations.display.cancel.message.success"));
                    return $this->redirectToRoute("displayEvent", array("token" => $eventInvitationToCancel->getEvent()->getToken()));
                }
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitation.message.error.unauthorized_cancel");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_cancel"));
            if ($eventInvitationToCancel != null) {
                return $this->redirectToRoute("displayEvent", array("token" => $eventInvitationToCancel->getEvent()->getToken()));
            } else {
                return $this->redirectToRoute("home");
            }
        }
    }

    /**
     * @Route("/archive/{userEventInvitationToken}/{archived}", name="archiveUserEventInvitation" )
     * @ParamConverter("userEventInvitation", class="AppBundle:Event\EventInvitation", options={"mapping": {"userEventInvitationToken":"token"}})
     */
    public function archiveEventInvitationAction(EventInvitation $userEventInvitation, $archived, Request $request)
    {
        $this->denyAccessUnlessGranted(EventInvitationVoter::ARCHIVE, $userEventInvitation);
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        if ($eventInvitationManager->archiveEventInvitation($userEventInvitation, $archived)) {
            if ($request->isXmlHttpRequest()) {
                if ($archived) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("eventInvitations.action.message.archive.success");
                } else {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("eventInvitations.action.message.unarchive.success");
                }

                $userEventInvitationsUpcomings = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser(), "#upcomingEvents");
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_HTML]["#upcomingEvents"] =
                    $this->renderView("@App/EventInvitation/partials/event_invitations_table.html.twig", array(
                        "eventInvitations" => $userEventInvitationsUpcomings,
                        "userEventTabId" => "#upcomingEvents"
                    ));

                $userEventInvitationsPassedArchived = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser(), "#passedArchivedEvents");
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_HTML]["#passedArchivedEvents"] =
                    $this->renderView("@App/EventInvitation/partials/event_invitations_table.html.twig", array(
                        "eventInvitations" => $userEventInvitationsPassedArchived,
                        "userEventTabId" => "#passedArchivedEvents"
                    ));

                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                if ($archived) {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("eventInvitations.action.message.archive.success"));
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator")->trans("eventInvitations.action.message.unarchive.success"));
                }
                return $this->redirectToRoute("displayUserEvents");
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                if ($archived) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitations.action.message.archive.error");
                } else {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("eventInvitations.action.message.unarchive.error");
                }
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                if ($archived) {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitations.action.message.archive.error"));
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitations.action.message.unarchive.error"));
                }
                return $this->redirectToRoute("displayUserEvents");
            }
        }
    }

    /**
     * @Route("/show-all", name="displayUserEvents")
     */
    public function displayUserEventsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $userEventInvitations = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser(), "#upcomingEvents");
        return $this->render("@App/EventInvitation/user_event_invitations.html.twig", array(
            "eventInvitations" => $userEventInvitations
        ));
    }

    /**
     * @Route("/show-tab", name="displayUserEventsTab")
     */
    public function displayUserEventsTabActionPartial(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $userEventTabId = $request->get('userEventTabId');
            if (empty($userEventTabId)) {
                $userEventTabId = '#upcomingEvents';
            }
            $userEventInvitationsFiltered = $this->get('at.manager.application_user')->getUserEventInvitations($this->getUser(), $userEventTabId);

            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO][$userEventTabId] =
                $this->renderView("@App/EventInvitation/partials/event_invitations_table.html.twig", array(
                    "eventInvitations" => $userEventInvitationsFiltered,
                    "userEventTabId" => $userEventTabId
                ));
            return new AppJsonResponse($data, Response::HTTP_OK);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('displayUserEvents');
        }
    }
}