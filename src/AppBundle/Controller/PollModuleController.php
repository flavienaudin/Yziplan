<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 09:55
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\FormUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PollModuleController extends Controller
{
    /**
     * @Route("/{_locale}/pollproposal/edition/{pollProposalId}/{moduleInvitationToken}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="pollProposalEditionForm")
     * @ParamConverter("pollProposal", class="AppBundle:Module\PollProposal", options={"id" = "pollProposalId"})
     * @ParamConverter("moduleInvitation", class="AppBundle:Module\ModuleInvitation", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function pollProposalEditionFormAction(PollProposal $pollProposal, ModuleInvitation $moduleInvitation, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($moduleInvitation != $pollProposal->getCreator()) {
                $data['messages'][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("global.error.unauthorized_access");
                return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
            $pollProposalEditionForm = $this->get("at.manager.pollproposal")->createModuleForm($pollProposal);
            $pollProposalEditionForm->handleRequest($request);
            if ($pollProposalEditionForm->isSubmitted()) {
                if ($pollProposalEditionForm->isValid()) {
                    $moduleManager = $this->get("at.manager.module");
                    $pollProposal = $moduleManager->treatPollProposalForm($pollProposalEditionForm, $pollProposal->getPollModule()->getModule());
                    $data['messages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                    $data['htmlContent'] = $moduleManager->displayPollProposalRowPartial($pollProposal, $moduleInvitation->getEventInvitation());
                    return new JsonResponse($data, Response::HTTP_OK);
                } else {
                    $data["formErrors"] = array();
                    foreach ($pollProposalEditionForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            }
            $data['htmlContent'] = $this->renderView(
                '@App/Event/module/pollModulePartials/pollProposalFormModal.html.twig', array(
                    'pollProposalForm' => $pollProposalEditionForm->createView(),
                    'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                    'edition' => true,
                    'pollProposal' => $pollProposal,
                    'userModuleInvitation' => $moduleInvitation
                )
            );
            return new JsonResponse($data, Response::HTTP_OK);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute("home");
        }
    }

    /**
     * @Route("/{_locale}/pollproposal/remove/{pollProposalId}/{moduleInvitationToken}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="removePollProposal")
     * @ParamConverter("pollProposal", class="AppBundle:Module\PollProposal", options={"id" = "pollProposalId"})
     * @ParamConverter("moduleInvitation", class="AppBundle:Module\ModuleInvitation", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function removePollProposalAction(PollProposal $pollProposal, ModuleInvitation $moduleInvitation, Request $request)
    {
        if ($moduleInvitation == $pollProposal->getCreator()) {
            if ($request->isXmlHttpRequest()) {
                $moduleManager = $this->get("at.manager.module");
                $moduleManager->removePollProposal($pollProposal);
                $data['actionResult'] = true;
                $data['messsages'][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                return new JsonResponse($data, Response::HTTP_OK);
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