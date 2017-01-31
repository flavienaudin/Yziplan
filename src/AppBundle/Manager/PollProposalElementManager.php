<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 05/09/2016
 * Time: 13:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Form\Module\PollProposalType;
use AppBundle\Utils\enum\PollElementType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;


class PollProposalElementManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var PollProposalElement */
    private $pollProposalElement;

    public function __construct(EntityManager $doctrine, FormFactoryInterface $formFactory, EngineInterface $templating)
    {
        $this->entityManager = $doctrine;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }

    /**
     * @return PollProposalElement
     */
    public function getPollProposalElement()
    {
        return $this->pollProposalElement;
    }

    /**
     * @param PollProposalElement $pollProposalElement
     */
    public function setPollProposalElement($pollProposalElement)
    {
        $this->pollProposalElement = $pollProposalElement;
    }

    /**
     * @param FormInterface $pollProposalForm
     * @param Module $module
     * @return PollProposal|mixed
     */
    public function treatPollProposalElementForm(FormInterface $pollProposalElementForm, PollProposal $pollProposal)
    {
        $this->pollProposalElement = $pollProposalElementForm->getData();
        if ($this->pollProposalElement->getPollElement()->getType() == PollElementType::DATETIME) {
            $datetimeValue = new DateTime();
            /** @var array $valDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valDate = $pollProposalElementForm->get('startDate')->getData();
            /** @var array $valTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valTime = $pollProposalElementForm->get('startTime')->getData();
            if (!empty($valDate)) {
                $datetimeValue->setDate($valDate['year'], $valDate['month'], $valDate['day']);
            }
            if (!empty($valTime) && !empty($valTime['hour']) && !empty($valTime['minute'])) {
                $datetimeValue->setTime($valTime['hour'], $valTime['minute']);
                $this->pollProposalElement->setTime(true);
            } else {
                $this->pollProposalElement->setTime(false);
            }
            $this->pollProposalElement->setValDatetime($datetimeValue);
        }
        if ($this->pollProposalElement->getPollElement()->getType() == PollElementType::END_DATETIME) {
            $datetimeValue = new DateTime();
            /** @var array $valDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valDate = $pollProposalElementForm->get('endDate')->getData();
            /** @var array $valTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valTime = $pollProposalElementForm->get('endTime')->getData();
            if (!empty($valDate) && !empty($valDate['year'])) {
                $datetimeValue->setDate($valDate['year'], $valDate['month'], $valDate['day']);
                $this->pollProposalElement->setEndDate(true);
            } else {
                $this->pollProposalElement->setEndDate(false);
            }
            if (!empty($valTime) && !empty($valTime['hour']) && !empty($valTime['minute'])) {
                $datetimeValue->setTime($valTime['hour'], $valTime['minute']);
                $this->pollProposalElement->setEndTime(true);
            } else {
                $this->pollProposalElement->setEndTime(false);
            }
            $this->pollProposalElement->setValEndDatetime($datetimeValue);
        }

        $pollProposal->addPollProposalElement($this->pollProposalElement);
        return $this->pollProposalElement;
    }
}