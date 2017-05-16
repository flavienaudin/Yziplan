<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/07/2016
 * Time: 16:17
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalResponse;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;

class PollProposalResponseManager
{

    /** @var EntityManager */
    private $entityManager;

    /** @var EngineInterface */
    private $templating;

    /** @var $pollProposalResponse PollProposalResponse */
    private $pollProposalResponse;

    public function __construct(EntityManager $doctrine, EngineInterface $templating)
    {
        $this->entityManager = $doctrine;
        $this->templating = $templating;
    }

    public function initializePollProposalResponse(ModuleInvitation $moduleInvitation, PollProposal $pollProposal, $value)
    {
        $this->pollProposalResponse = new PollProposalResponse();
        $this->pollProposalResponse->setAnswer($value);
        $pollProposal->addPollProposalResponse($this->pollProposalResponse);
        $moduleInvitation->addPollProposalResponse($this->pollProposalResponse);
    }

    /**
     * Remet à null toutes les réponses au sondage.
     * NB : Réponses non persitées
     * @param PollModule $pollModule Le sondage à mettre à jour
     */
    public function resetPollProposalResponse(PollModule $pollModule)
    {
        $moduleInvitationsUpdated = array();
        /** @var PollProposal $pollProposal */
        foreach ($pollModule->getPollProposals() as $pollProposal) {
            /** @var PollProposalResponse $pollProposalResponse */
            foreach ($pollProposal->getPollProposalResponses() as $pollProposalResponse) {
                if ($pollProposalResponse->getAnswer() != null) {
                    $pollProposalResponse->setAnswer(null);
                    $moduleInvitation = $pollProposalResponse->getModuleInvitation();
                    if ($moduleInvitation->getStatus() == ModuleInvitationStatus::INVITED && $moduleInvitation->getEventInvitation()->getStatus() != EventInvitationStatus::CANCELLED) {
                        $moduleInvitationsUpdated[$moduleInvitation->getId()] = $moduleInvitation;
                    }
                }
            }
        }
        return $moduleInvitationsUpdated;
    }

    public function answerPollModuleProposal(ModuleInvitation $moduleInvitation, $pollProposalId, $value)
    {
        $this->pollProposalResponse = null;
        foreach ($moduleInvitation->getPollProposalResponses() as $pollProposalResponse) {
            if ($pollProposalResponse->getPollProposal() != null && $pollProposalResponse->getPollProposal()->getId() == $pollProposalId) {
                $this->pollProposalResponse = $pollProposalResponse;
                $this->pollProposalResponse->setAnswer($value);
            }
        }
        if ($this->pollProposalResponse == null) {
            $pollProposal = $moduleInvitation->getModule()->getPollModule()->getPollProposalById($pollProposalId);
            if ($pollProposal != null) {
                $this->initializePollProposalResponse($moduleInvitation, $pollProposal, $value);
            } else {
                return false;
            }
        }

        $this->entityManager->persist($this->pollProposalResponse);
        $this->entityManager->flush();
        return true;
    }

    /**
     * @param Integer $pollProposalId
     * @return string
     */
    public function displayPollProposalRowResultPartial($pollProposalId)
    {
        $pollProposal = $this->entityManager->getRepository(PollProposal::class)->findOneBy(array('id' => $pollProposalId));

        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseRowDisplay_result.html.twig", array(
            'pollProposal' => $pollProposal,
            'moduleInvitations' => $pollProposal->getPollModule()->getModule()->getModuleInvitations()
        ));
    }

}