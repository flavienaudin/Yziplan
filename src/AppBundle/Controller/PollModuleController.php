<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 09:55
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Manager\EventManager;
use AppBundle\Manager\PollProposalManager;
use AppBundle\Security\PollProposalVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\PollElementType;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PollModuleController
 * @package AppBundle\Controller
 * @Route("/{_locale}/pollmodule", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class PollModuleController extends Controller
{

    /**
     * TODO en attendant de séparer les soumissions des formulaires de l'action "displayEvent"
     * Soumission du formulaire d'ajout d'une proposition à un module
     * @ Route("/pollproposal/add/{moduleToken}", name="pollProposalAddForm")
     * @ ParamConverter("module", class="AppBundle:Event\Module", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function pollProposalAddFormAction(Module $module, Request $request)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->get('at.manager.event');
        $currentEvent = $module->getEvent();
        $eventManager->setEvent($currentEvent);

        if ($module->getPollModule() == null) {
            // wrong data
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.invalid_form'));
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
            }
        }

        /////////////////////////////////////
        // User EventInvitation management //
        /////////////////////////////////////
        $eventInvitationManager = $this->get("at.manager.event_invitation");
        $userEventInvitation = $eventInvitationManager->retrieveUserEventInvitation($currentEvent, false, false, $this->getUser());
        if ($userEventInvitation == null) {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("eventInvitation.message.error.unauthorized_access"));
            return $this->redirectToRoute("home");
        } else {
            /* TODO : invitation annulée : désactiver les formulaires */
            if ($userEventInvitation->getStatus() == EventInvitationStatus::CANCELLED) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::WARNING_TYPE][] = $this->get('translator')->trans('eventInvitation.message.warning.invitation_cancelled');
                return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
            }

            if ($module->getStatus() != ModuleStatus::DELETED && $module->getStatus() != ModuleStatus::ARCHIVED) {
                $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);
                if ($userModuleInvitation != null && $userModuleInvitation->getStatus() != ModuleInvitationStatus::CANCELLED) {

                    // TODO Vérifier les autorisations d'ajouter des propositions au module

                    /** @var PollProposalManager $pollProposalManager */
                    $pollProposalManager = $this->get("at.manager.pollproposal");

                    /** @var FormInterface $pollProposalAddForm */
                    $pollProposalAddForm = $pollProposalManager->createPollProposalAddForm($module->getPollModule());
                    $pollProposalAddForm->handleRequest($request);
                    if ($pollProposalAddForm->isSubmitted()) {
                        if ($request->isXmlHttpRequest()) {
                            if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                                // Vérification serveur de la validité de l'invitation
                                $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                            } else if ($pollProposalAddForm->isValid()) {
                                $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalAddForm, $module);
                                $data[AppJsonResponse::DATA] = $pollProposalManager->displayPollProposalRowPartial($pollProposal, $userEventInvitation);

                                // Form reset
                                $pollProposalAddForm = $pollProposalManager->createPollProposalAddForm($module->getPollModule());
                                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#add_pp_fm_' . $module->getToken() . '_formContainer'] =
                                    $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                        'userModuleInvitation' => $userModuleInvitation,
                                        'pollProposalForm' => $pollProposalAddForm->createView(),
                                        'pp_form_modal_prefix' => "add_pp_fm_" . $module->getToken(),
                                        'edition' => false
                                    ));
                                return new AppJsonResponse($data, Response::HTTP_OK);
                            } else {
                                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#add_pp_fm_' . $module->getToken() . '_formContainer'] =
                                    $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                        'userModuleInvitation' => $userModuleInvitation,
                                        'pollProposalForm' => $pollProposalAddForm->createView(),
                                        'pp_form_modal_prefix' => "add_pp_fm_" . $module->getToken(),
                                        'edition' => false
                                    ));
                                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                            }
                        } else {
                            if ($userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $userEventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                                // Vérification serveur de la validité de l'invitation
                                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                                return new RedirectResponse($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $module->getToken());
                            } else if ($pollProposalAddForm->isValid()) {
                                $pollProposalManager->treatPollProposalForm($pollProposalAddForm, $module);
                                return new RedirectResponse($this->generateUrl('displayEvent', array('token' => $currentEvent->getToken())) . '#module-' . $module->getToken());
                            }
                        }
                    }
                }
            }

            // wrong data
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('global.error.invalid_form');
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.invalid_form'));
                return $this->redirectToRoute('displayEvent', array('token' => $currentEvent->getToken()));
            }
        }
    }

    /**
     * @Route("/pollproposal/edition/{pollProposalId}/{moduleInvitationToken}", name="pollProposalEditionForm")
     * @ParamConverter("pollProposal", class="AppBundle:Module\PollProposal", options={"id" = "pollProposalId"})
     * @ParamConverter("moduleInvitation", class="AppBundle:Event\ModuleInvitation", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function pollProposalEditionFormAction(PollProposal $pollProposal, ModuleInvitation $moduleInvitation, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if (!$this->isGranted(PollProposalVoter::EDIT, array($pollProposal, $moduleInvitation))) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
            $pollProposalManager = $this->get("at.manager.pollproposal");
            $pollProposalEditionForm = $pollProposalManager->createPollProposalForm($pollProposal);
            $pollProposalEditionForm->handleRequest($request);
            if ($pollProposalEditionForm->isSubmitted()) {
                if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_VALIDATION
                    || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER
                ) {
                    // Vérification serveur de la validité de l'invitation
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    if ($pollProposalEditionForm->isValid()) {
                        $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalEditionForm, $moduleInvitation->getModule());
                        // Mise à jour des pollProposalElement avec un Fichier pour
                        $em = $this->get('doctrine.orm.entity_manager');
                        $em->refresh($pollProposal);

                        $pollProposalEditionForm = $pollProposalManager->createPollProposalForm($pollProposal);
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]["#pollProposalEdition_" . $pollProposal->getId() . "_form_id"] =
                            $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                    'userModuleInvitation' => $moduleInvitation,
                                    'pollModuleOptions'=> array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
                                    'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                                    'edition' => true
                                )
                            );
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#pp_display_row_' . $pollProposal->getId()] =
                            $pollProposalManager->displayPollProposalRowPartial($pollProposal, $moduleInvitation->getEventInvitation());
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#pollProposalEdition_' . $pollProposal->getId() . '_formContainer'] =
                            $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                'userModuleInvitation' => $moduleInvitation,
                                'pollModuleOptions'=> array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
                                'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                                'edition' => true
                            ));
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }
            }
            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]['#modal-container-block-' . $pollProposal->getPollModule()->getModule()->getToken()] =
                $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form_modal.html.twig', array(
                        'userModuleInvitation' => $moduleInvitation,
                        'pollModuleOptions'=> array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
                        'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                        'edition' => true
                    )
                );
            return new AppJsonResponse($data, Response::HTTP_OK);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute("home");
        }
    }

    /**
     * @Route("/pollproposal/remove/{pollProposalId}/{moduleInvitationToken}", name="removePollProposal")
     * @ParamConverter("pollProposal", class="AppBundle:Module\PollProposal", options={"id" = "pollProposalId"})
     * @ParamConverter("moduleInvitation", class="AppBundle:Event\ModuleInvitation", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function removePollProposalAction(PollProposal $pollProposal, ModuleInvitation $moduleInvitation, Request $request)
    {
        if ($this->isGranted(PollProposalVoter::DELETE, array($pollProposal, $moduleInvitation))) {
            if ($request->isXmlHttpRequest()) {
                if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_VALIDATION
                    || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER
                ) {
                    // Vérification serveur de la validité de l'invitation
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $pollProposalManager = $this->get("at.manager.pollproposal");
                    $pollProposalManager->removePollProposal($pollProposal);
                    $data[AppJsonResponse::DATA]['pollProposalId'] = $pollProposal->getId();
                    return new AppJsonResponse($data, Response::HTTP_OK);
                }
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirectToRoute("home");
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.unauthorized_access'));
                return $this->redirectToRoute("home");
            }
        }
    }

    /**
     * @Route("/result/display/{moduleToken}", name="pollModuleDisplayResultTable")
     * @ParamConverter("module", class="AppBundle:Event\Module", options={"mapping" = {"moduleToken":"token"}})
     */
    public function displayResultTableAction(Module $module, Request $request)
    {
        $eventInvitation = $this->get("at.manager.event_invitation")->retrieveUserEventInvitation($module->getEvent(), false, false, $this->getUser());
        if ($request->isXmlHttpRequest()) {
            $userModuleInvitation = $eventInvitation->getModuleInvitationForModule($module);
            if ($eventInvitation != null && $userModuleInvitation != null && $userModuleInvitation->getStatus() != ModuleInvitationStatus::CANCELLED) {
                $moduleManager = $this->get("at.manager.module");
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#pollModuleDisplayAllResult_' . $module->getToken() . '_container'] =
                    $moduleManager->displayPollModuleResultTable($module);
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("event.error.message.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute("home");
        }


    }
}