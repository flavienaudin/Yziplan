<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/07/2016
 * Time: 14:55
 */

namespace AppBundle\Controller;


use AppBundle\Security\ModuleInvitationVoter;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ModuleInvitationController
 * @package AppBundle\Controller
 * @Route("/{_locale}/module-invitation", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class ModuleInvitationController extends Controller
{
    /**
     * @Route("/answer-pollmodule-proposal", name="answerPollModuleProposal", methods={"POST"})
     */
    public function answerPollModuleProposalAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->request->has("pollProposalId") && $request->request->has('userModuleInvitationToken') && $request->request->has('value')) {
                $moduleInvitationManager = $this->get("at.manager.module_invitation");
                $moduleInvitation = $moduleInvitationManager->retrieveModuleInvitationByToken($request->request->get('userModuleInvitationToken'));

                if ($moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_VALIDATION ||
                    $moduleInvitation->getEventInvitation()->getStatus() == EventInvitationStatus::AWAITING_ANSWER
                ) {
                    // Vérification serveur de la validité de l'invitation
                    $data[AppJsonResponse::DATA]['eventInvitationValid'] = false;
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans("event.error.message.valide_guestname_required");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else if ($this->isGranted(ModuleInvitationVoter::EDIT, $moduleInvitation)) {
                    $pollroposalResponseManager = $this->get("at.manager.pollproposal_response");
                    if($pollroposalResponseManager->answerPollModuleProposal($moduleInvitation, $request->request->get('pollProposalId'), $request->request->get('value'))) {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#resultCellPollProposal_' . $request->request->get('pollProposalId')] =
                            $pollroposalResponseManager->displayPollProposalRowResultPartial($request->request->get('pollProposalId'));
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    }else{
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('moduleInvitation.error.message.missing_data');
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('moduleInvitation.error.message.unauthorized_access');
                    return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
                }
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get('translator')->trans('moduleInvitation.error.message.missing_data');
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
            return $this->redirectToRoute('home');
        }
    }
}