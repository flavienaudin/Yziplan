<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 09:55
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            if ($moduleInvitation != $pollProposal->getCreator()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
            $pollProposalManager = $this->get("at.manager.pollproposal");
            $pollProposalEditionForm = $pollProposalManager->createPollProposalForm($pollProposal);
            $pollProposalEditionForm->handleRequest($request);
            if ($pollProposalEditionForm->isSubmitted()) {
                if ($pollProposalEditionForm->isValid()) {
                    $pollProposal = $pollProposalManager->treatPollProposalForm($pollProposalEditionForm);
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#pp_display_row_' . $pollProposal->getId()] =
                        $pollProposalManager->displayPollProposalRowPartial($pollProposal, $moduleInvitation->getEventInvitation());
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#pollProposalEdition_' . $pollProposal->getId() . '_formContainer'] =
                        $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form.html.twig', array(
                            'userModuleInvitation' => $moduleInvitation,
                            'pollProposalForm' => $pollProposalEditionForm->createView(),
                            'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                            'edition' => true
                        ));
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            }
            $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_APPEND_TO]['#modal-container-block-' . $pollProposal->getPollModule()->getModule()->getToken()] =
                $this->renderView('@App/Event/module/pollModulePartials/pollProposal_form_modal.html.twig', array(
                        'userModuleInvitation' => $moduleInvitation,
                        'pollProposalForm' => $pollProposalEditionForm->createView(),
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
        if ($moduleInvitation == $pollProposal->getCreator()) {
            if ($request->isXmlHttpRequest()) {
                $pollProposalManager = $this->get("at.manager.pollproposal");
                $pollProposalManager->removePollProposal($pollProposal);
                $data[AppJsonResponse::DATA]['actionResult'] = true;
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                return new AppJsonResponse($data, Response::HTTP_OK);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirectToRoute("home");
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.unauthorized_access'));
            return $this->redirectToRoute("home");
        }
    }
}