<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 05/09/2016
 * Time: 13:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
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
    public function treatPollProposalElementForm(FormInterface $pollProposalElementForm)
    {
        $this->pollProposalElement = $pollProposalElementForm->getData();
        if ($this->pollProposalElement->getPollElement()->getType() == PollElementType::DATETIME) {
            $datetimeValue = new DateTime();
            /** @var array $valDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valDate = $pollProposalElementForm->get('startDate')->getData();
            /** @var array $valTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valTime = $pollProposalElementForm->get('startTime')->getData();

            if ($this->castArrayValuesToInt($valDate)) {
                $datetimeValue->setDate($valDate['year'], $valDate['month'], $valDate['day']);
            }
            if ($this->castArrayValuesToInt($valTime)) {
                $datetimeValue->setTime((int)$valTime['hour'], (int)$valTime['minute']);
                $this->pollProposalElement->setTime(true);
            } else {
                $this->pollProposalElement->setTime(false);
            }
            $this->pollProposalElement->setValDatetime($datetimeValue);
        }else if ($this->pollProposalElement->getPollElement()->getType() == PollElementType::END_DATETIME) {
            $datetimeValue = new DateTime();
            /** @var array $valDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valDate = $pollProposalElementForm->get('endDate')->getData();
            /** @var array $valTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valTime = $pollProposalElementForm->get('endTime')->getData();

            if ($this->castArrayValuesToInt($valDate)) {
                $datetimeValue->setDate($valDate['year'], $valDate['month'], $valDate['day']);
                $this->pollProposalElement->setEndDate(true);
            } else {
                $this->pollProposalElement->setEndDate(false);
            }
            if ($this->castArrayValuesToInt($valTime)) {
                $datetimeValue->setTime($valTime['hour'], $valTime['minute']);
                $this->pollProposalElement->setEndTime(true);
            } else {
                $this->pollProposalElement->setEndTime(false);
            }
            $this->pollProposalElement->setValEndDatetime($datetimeValue);
        }else if ($this->pollProposalElement->getPollElement()->getType() == PollElementType::END_DATETIME) {

        }
        return $this->pollProposalElement;
    }

    /**
     * Cast to int each values in the array
     * @param $array array with all values to cast
     * @return bool true if all values were casted Else false
     */
    private function castArrayValuesToInt(&$array)
    {
        $valid = true;
        $newValues = array();
        foreach (array_keys($array) as $key) {
            if ($array[$key] != "") {
                $newValues[$key] = (int)$array[$key];
            } else {
                $valid &= false;
            }
        }
        if ($valid) {
            foreach (array_keys($array) as $key) {
                $array[$key] = $newValues[$key];
            }
            return true;
        } else {
            return false;
        }
    }


}