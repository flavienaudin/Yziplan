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
use AppBundle\Utils\FormUtils;
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
            $pollProposalEditionForm = $this->get("at.manager.pollproposal")->createModuleForm($pollProposal);
            $pollProposalEditionForm->handleRequest($request);
            if ($pollProposalEditionForm->isSubmitted()) {
                if ($pollProposalEditionForm->isValid()) {
                    $moduleManager = $this->get("at.manager.module");
                    $pollProposal = $moduleManager->treatPollProposalForm($pollProposalEditionForm, $pollProposal->getPollModule()->getModule());
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("global.success.data_saved");
                    // TODO use AppJsonResponse::HTML_CONTENTS
                    $data['htmlContent'] = $moduleManager->displayPollProposalRowPartial($pollProposal, $moduleInvitation->getEventInvitation());
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    // TODO use AppJsonResponse::HTML_CONTENTS to replace the FORM
                    $data["formErrors"] = array();
                    foreach ($pollProposalEditionForm->getErrors(true) as $error) {
                        $data["formErrors"][FormUtils::getFullFormErrorFieldName($error)] = $error->getMessage();
                    }
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            }
            // TODO use AppJsonResponse::HTML_CONTENTS
            $data['htmlContent'] = $this->renderView(
                '@App/Event/module/pollModulePartials/pollProposalFormModal.html.twig', array(
                    'pollProposalForm' => $pollProposalEditionForm->createView(),
                    'pp_form_modal_prefix' => 'pollProposalEdition_' . $pollProposal->getId(),
                    'edition' => true,
                    'pollProposal' => $pollProposal,
                    'userModuleInvitation' => $moduleInvitation
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
     * @ParamConverter("moduleInvitation", class="AppBundle:Module\ModuleInvitation", options={"mapping" = {"moduleInvitationToken":"token"}})
     */
    public function removePollProposalAction(PollProposal $pollProposal, ModuleInvitation $moduleInvitation, Request $request)
    {
        if ($moduleInvitation == $pollProposal->getCreator()) {
            if ($request->isXmlHttpRequest()) {
                $moduleManager = $this->get("at.manager.module");
                $moduleManager->removePollProposal($pollProposal);
                $data['actionResult'] = true;
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