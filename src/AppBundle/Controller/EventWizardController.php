<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/03/2017
 * Time: 16:47
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\Event;
use AppBundle\Manager\EventManager;
use AppBundle\Security\EventVoter;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class EventWizardController
 * @package AppBundle\Controller
 * @Route("/{_locale}/event/wizard", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class EventWizardController extends Controller
{
    const WIZARD_NEW_EVENT_STEP_MAIN = "main";
    const WIZARD_NEW_EVENT_STEP_PROFILE = "profile";
    const WIZARD_NEW_EVENT_STEP_ADD_MODULE = "addmodule";
    const WIZARD_NEW_EVENT_STEP_INVITATIONS = "invitations";
    const WIZARD_NEW_EVENT_STEP_TEMPLATE_SETTINGS = "templateSettings";


    /**
     * @Route("/1/{token}", name="wizardNewEventStep1")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function wizardEventStep1Action(Event $currentEvent, Request $request)
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

            /** @var FormInterface $eventForm */
            $eventForm = $eventManager->initEventForm();
            $eventForm->handleRequest($request);
            if ($eventForm->isSubmitted()) {
                if ($eventForm->isValid()) {
                    $currentEvent = $eventManager->treatEventFormSubmission($eventForm, $userEventInvitation);
                    return $this->redirectToRoute('wizardNewEventStep2', array('token' => $currentEvent->getToken()));
                }
            }
            return $this->render("@App/Event/wizard/step_event_main.html.twig", array(
                'event' => $currentEvent,
                'userEventInvitation' => $userEventInvitation,
                'eventForm' => $eventForm->createView()
            ));
        }
    }

    /**
     * @Route("/2/{token}", name="wizardNewEventStep2")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function wizardEventStep2Action(Event $currentEvent, Request $request)
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

            $modules = $eventManager->getModulesToDisplay($userEventInvitation);
            $response = $eventManager->treatModulesToDisplay($currentEvent, $modules, $userEventInvitation, $request);
            if ($response != null) {
                // Response due to module's form submission
                return $response;
            }
            return $this->render("@App/Event/wizard/step_event_addModule.html.twig", array('event' => $currentEvent, 'modules' => $modules, 'userEventInvitation' => $userEventInvitation));
        }
    }

    /**
     * @Route("/3/{token}", name="wizardNewEventStep3")
     * @ParamConverter("currentEvent", class="AppBundle:Event\Event")
     */
    public function wizardEventStep3Action(Event $currentEvent, Request $request)
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

            // Profile Form :
            /** @var FormInterface $eventForm */
            $eventProfileForm = $eventInvitationManager->createEventInvitationForm();
            $eventProfileForm->handleRequest($request);
            if ($eventProfileForm->isSubmitted()) {
                if ($request->isXmlHttpRequest()) {
                    if ($eventProfileForm->isValid()) {
                        $userEventInvitation = $eventInvitationManager->treatEventInvitationFormSubmission($eventProfileForm);

                        // Update the form with the updated userEventInvitation
                        $eventProfileForm = $eventInvitationManager->createEventInvitationForm();
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitationProfile_formContainer'] =
                            $this->renderView("@App/Event/wizard/wizard_eventInvitation_profile_form.html.twig", array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationForm' => $eventProfileForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#eventInvitationProfile_formContainer'] =
                            $this->renderView('@App/Event/wizard/wizard_eventInvitation_profile_form.html.twig', array(
                                'userEventInvitation' => $userEventInvitation,
                                'userEventInvitationForm' => $eventProfileForm->createView()
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($eventProfileForm->isValid()) {
                        $eventInvitationManager->treatEventInvitationFormSubmission($eventProfileForm);
                        return $this->redirectToRoute('wizardNewEventStep3', array('token' => $currentEvent->getToken()));
                    }
                }
            }

            $templateOptions = array(
                'event' => $currentEvent,
                'userEventInvitation' => $userEventInvitation,
                'userEventInvitationForm' => $eventProfileForm->createView()
            );

            $eventInvitationsForm = null;
            if (!$currentEvent->isTemplate()) {
                /** @var FormInterface $eventInvitationsForm */
                $eventInvitationsForm = $eventManager->createEventInvitationsForm();
                $eventInvitationsForm->handleRequest($request);
                if ($eventInvitationsForm->isSubmitted()) {
                    if ($eventInvitationsForm->isValid()) {
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
                        }
                        return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
                    }
                }
                $templateOptions['invitationsForm'] = $eventInvitationsForm->createView();
            }
            return $this->render("@App/Event/wizard/step_event_recapitulatif.html.twig", $templateOptions);
        }
    }
}