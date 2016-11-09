<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/09/2016
 * Time: 13:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Form\Module\PollProposalType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;


class PollProposalManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var PollProposal */
    private $pollProposal;

    public function __construct(EntityManager $doctrine, FormFactoryInterface $formFactory, EngineInterface $templating)
    {
        $this->entityManager = $doctrine;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    /**
     * @return PollProposal
     */
    public function getPollProposal()
    {
        return $this->pollProposal;
    }

    /**
     * @param PollProposal $pollProposal
     * @return PollProposalManager
     */
    public function setPollProposal($pollProposal)
    {
        $this->pollProposal = $pollProposal;
        return $this;
    }

    /**
     * @param PollProposal $pollProposal
     * @param EventInvitation $userEventInvitation
     * @return string
     */
    public function displayPollProposalRowPartial(PollProposal $pollProposal, EventInvitation $userEventInvitation)
    {
        $userModuleInvitation = null;
        if ($userEventInvitation != null) {
            $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($pollProposal->getPollModule()->getModule());
        }
        return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseRowDisplay.html.twig", array(
            'pollProposal' => $pollProposal,
            'moduleInvitations' => $pollProposal->getPollModule()->getModule()->getModuleInvitations(),
            'userModuleInvitation' => $userModuleInvitation
        ));
    }

    /**
     * @param PollModule $pollModule
     * @param ModuleInvitation $userModuleInvitation
     * @return FormInterface
     */
    public function createPollProposalAddForm(PollModule $pollModule, ModuleInvitation $userModuleInvitation)
    {
        $this->pollProposal = new PollProposal();
        $this->pollProposal->setCreator($userModuleInvitation);
        $this->pollProposal->initializeWithPollModule($pollModule);

        return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalType::class, $this->pollProposal);
    }

    /**
     * @param PollProposal $pollProposal
     * @return FormInterface
     */
    public function createPollProposalForm(PollProposal $pollProposal)
    {
        $this->pollProposal = $pollProposal;
        return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalType::class, $pollProposal);
    }

    /**
     * @param FormInterface $pollProposalForm
     * @param Module $module
     * @return PollProposal|mixed
     */
    public function treatPollProposalForm(FormInterface $pollProposalForm, Module $module = null)
    {
        $this->pollProposal = $pollProposalForm->getData();
        if ($module != null && $this->pollProposal->getPollModule() == null) {
            $module->getPollModule()->addPollProposal($this->pollProposal);
        }
        $this->entityManager->persist($this->pollProposal);
        $this->entityManager->flush();
        return $this->pollProposal;
    }

    /**
     * @param PollProposal $pollProposal The PollProposal to remove
     * @return PollProposal
     */
    public function removePollProposal(PollProposal $pollProposal)
    {
        $this->pollProposal = $pollProposal;
        $this->pollProposal->setDeleted(true);
        $this->entityManager->persist($this->pollProposal);
        $this->entityManager->flush();
        return $this->pollProposal;
    }
}