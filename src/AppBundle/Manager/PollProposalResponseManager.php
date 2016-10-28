<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/07/2016
 * Time: 16:17
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalResponse;
use Doctrine\ORM\EntityManager;

class PollProposalResponseManager
{

    /** @var EntityManager */
    private $entityManager;

    /** @var $pollProposalResponse PollProposalResponse */
    private $pollProposalResponse;

    public function __construct(EntityManager $doctrine)
    {
        $this->entityManager = $doctrine;
    }

    public function initializePollProposalResponse(ModuleInvitation $moduleInvitation, PollProposal $pollProposal, $value)
    {
        $this->pollProposalResponse = new PollProposalResponse();
        $this->pollProposalResponse->setAnswer($value);
        $pollProposal->addPollProposalResponse($this->pollProposalResponse);
        $moduleInvitation->addPollProposalResponse($this->pollProposalResponse);
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
            $this->initializePollProposalResponse($moduleInvitation, $pollProposal, $value);
        }

        $this->entityManager->persist($this->pollProposalResponse);
        $this->entityManager->flush();
    }
}