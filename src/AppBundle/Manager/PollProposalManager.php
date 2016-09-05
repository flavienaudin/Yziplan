<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/09/2016
 * Time: 13:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\module\PollProposal;
use AppBundle\Form\PollProposalFormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Test\FormInterface;


class PollProposalManager
{

    /** @var FormFactory */
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param PollProposal $pollProposal
     * @return FormInterface
     */
    public function createModuleForm(PollProposal $pollProposal)
    {
        return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalFormType::class, $pollProposal);
    }

}