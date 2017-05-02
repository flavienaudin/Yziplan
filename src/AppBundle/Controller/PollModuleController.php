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
use AppBundle\Security\PollProposalVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class PollModuleController
 * @package AppBundle\Controller
 * @Route("/{_locale}/pollmodule", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class PollModuleController extends Controller
{
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
                if ($request->server->get('HTTP_REFERER') != $this->generateUrl('wizardNewEventStep2', array('token' => $moduleInvitation->getEventInvitation()->getEvent()->getToken()), UrlGenerator::ABSOLUTE_URL) &&
                    ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
                ) {
                    // Vérification serveur de la validité de l'invitation
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    if ($pollProposalEditionForm->isValid()) {
                        $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalEditionForm, $moduleInvitation->getModule(), $moduleInvitation);
                        // Mise à jour des pollProposalElement avec un Fichier pour
                        $em = $this->get('doctrine.orm.entity_manager');
                        $em->refresh($pollProposal);

                        $pollProposalEditionForm = $pollProposalManager->createPollProposalForm($pollProposal);
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]["#pollProposalEdition_" . $pollProposal->getId() . "_form_id"] =
                            $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                                    'userModuleInvitation' => $moduleInvitation,
                                    'pollModuleOptions' => array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
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
                                'pollModuleOptions' => array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
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
                        'pollModuleOptions' => array('pollProposalAddForm' => $pollProposalEditionForm->createView()),
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
                if ($request->server->get('HTTP_REFERER') != $this->generateUrl('wizardNewEventStep2', array('token' => $moduleInvitation->getEventInvitation()->getEvent()->getToken()),
                        UrlGenerator::ABSOLUTE_URL) &&
                    ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_VALIDATION
                        || $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
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