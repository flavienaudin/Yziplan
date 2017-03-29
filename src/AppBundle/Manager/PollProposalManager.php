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
use AppBundle\Form\Module\PollModule\PollProposalActivityType;
use AppBundle\Form\Module\PollModule\PollProposalWhatType;
use AppBundle\Form\Module\PollModule\PollProposalWhenCollectionType;
use AppBundle\Form\Module\PollModule\PollProposalType;
use AppBundle\Form\Module\PollModule\PollProposalWhenType;
use AppBundle\Form\Module\PollModule\PollProposalWhereType;
use AppBundle\Utils\enum\PollModuleType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;


class PollProposalManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var PollProposalElementManager */
    private $pollProposalElementManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var PollProposal */
    private $pollProposal;

    public function __construct(EntityManager $doctrine, FormFactoryInterface $formFactory, EngineInterface $templating, PollProposalElementManager $pollProposalElementManager)
    {
        $this->entityManager = $doctrine;
        $this->pollProposalElementManager = $pollProposalElementManager;
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
            'userModuleInvitation' => $userModuleInvitation,
            'moduleInvitations' => $pollProposal->getPollModule()->getModule()->getFilteredModuleInvitations()
        ));
    }

    /**
     * @param $pollProposals array of pPllProposal
     * @param EventInvitation $userEventInvitation
     * @return string
     * @internal param PollProposal $Array $pollProposal
     */
    public function displayPollProposalListRowPartial($pollProposals, EventInvitation $userEventInvitation)
    {
        if(!empty($pollProposals)) {
            $pollModule = $pollProposals[0]->getPollModule();
            $userModuleInvitation = null;
            if ($userEventInvitation != null) {
                $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($pollModule->getModule());
            }
            return $this->templating->render("@App/Event/module/pollModulePartials/pollProposalGuestResponseListRowDisplay.html.twig", array(
                'pollProposals' => $pollProposals,
                'userModuleInvitation' => $userModuleInvitation,
                'moduleInvitations' => $pollModule->getModule()->getFilteredModuleInvitations()
            ));
        }
        return null;
    }

    /**
     * @param PollModule $pollModule
     * @return FormInterface
     */
    public function createPollProposalAddForm(PollModule $pollModule)
    {
        if ($pollModule->getType() === PollModuleType::WHEN) {
            return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalWhenType::class);
        } else if ($pollModule->getType() === PollModuleType::WHAT || $pollModule->getType() === PollModuleType::WHO_BRINGS_WHAT) {
            return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalWhatType::class);
        } else if ($pollModule->getType() === PollModuleType::WHERE) {
            return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalWhereType::class);
        } else if ($pollModule->getType() === PollModuleType::ACTIVITY) {
            return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalActivityType::class);
        } else {
            return $this->formFactory->createNamed("add_poll_proposal_form_" . $pollModule->getModule()->getToken(), PollProposalType::class);
        }
    }

    /**
     * @param PollModule $pollModule
     * @return FormInterface
     */
    public function createPollProposalListAddForm(PollModule $pollModule)
    {
        if ($pollModule->getType() === PollModuleType::WHEN) {
            return $this->formFactory->createNamed("add_poll_proposal_list_form_" . $pollModule->getModule()->getToken(),
                PollProposalWhenCollectionType::class);
        } else {
            return null;
        }
    }

    /**
     * @param PollProposal $pollProposal
     * @return FormInterface
     */
    public function createPollProposalForm(PollProposal $pollProposal)
    {
        $this->pollProposal = $pollProposal;
        $pollModule = $this->pollProposal->getPollModule();
        if ($pollModule->getType() === PollModuleType::WHEN) {
            return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalWhenType::class, $pollProposal);
        } else if ($pollModule->getType() === PollModuleType::WHAT || $pollModule->getType() === PollModuleType::WHO_BRINGS_WHAT) {
            return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalWhatType::class, $pollProposal);
        } else if ($pollModule->getType() === PollModuleType::WHERE) {
            return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalWhereType::class, $pollProposal);
        } else if ($pollModule->getType() === PollModuleType::ACTIVITY) {
            return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalActivityType::class, $pollProposal);
        } else {
            return $this->formFactory->createNamed("poll_proposal_form_" . $pollProposal->getId(), PollProposalType::class, $pollProposal);
        }
    }

    /**
     * @param FormInterface $pollProposalForm
     * @param Module $module
     * @param ModuleInvitation $moduleInvitation
     * @return PollProposal|mixed
     */
    public function treatPollProposalForm(FormInterface $pollProposalForm, Module $module, ModuleInvitation $moduleInvitation = null)
    {
        /**
         * @var $pollProposal PollProposal
         */
        $pollProposal = $this->treatPollProposalWhenForm($pollProposalForm, $module->getPollModule()->getType());
        if ($pollProposal->getCreator() == null) {
            $pollProposal->setCreator($moduleInvitation);
        }
        if ($pollProposal->getPollModule() == null) {
            $pollProposal->setPollModule($module->getPollModule());
        }
        $this->entityManager->persist($pollProposal);
        $this->entityManager->flush();
        return $pollProposal;
    }

    /**
     * @param FormInterface $pollProposalListForm
     * @param Module $module
     * @param ModuleInvitation $moduleInvitation
     * @return Array(PollProposal)|mixed
     */
    public function treatPollProposalListForm(FormInterface $pollProposalListForm, Module $module, ModuleInvitation $moduleInvitation)
    {
        $result = array();
        foreach ($pollProposalListForm->get('pollProposalWhens') as $pollProposalForm) {
            $result[] = $this->treatPollProposalForm($pollProposalForm, $module, $moduleInvitation);
        }
        return $result;
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

    /**
     * @param FormInterface $pollProposalForm
     * @param  $pollModuleType
     * @return PollProposal|mixed
     * @internal param Module $module
     */
    public function treatPollProposalWhenForm(FormInterface $pollProposalForm, $pollModuleType)
    {
        $this->pollProposal = $pollProposalForm->getData();
        if ($pollModuleType == PollModuleType::WHEN) {
            $datetimeValue = new DateTime();
            /** @var array $valDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valDate = $pollProposalForm->get('startDate')->getData();
            /** @var array $valTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valTime = $pollProposalForm->get('startTime')->getData();

            if ($this->castArrayValuesToInt($valDate)) {
                $datetimeValue->setDate($valDate['year'], $valDate['month'], $valDate['day']);
            }
            if ($this->castArrayValuesToInt($valTime)) {
                $datetimeValue->setTime((int)$valTime['hour'], (int)$valTime['minute']);
                $this->pollProposal->setTime(true);
            } else {
                $this->pollProposal->setTime(false);
            }
            $this->pollProposal->setValDatetime($datetimeValue);

            //EndDate Case
            $endDatetimeValue = new DateTime();
            /** @var array $valEndDate (e.g. array('year' => 2011, 'month' => 06, 'day' => 05)) */
            $valEndDate = $pollProposalForm->get('endDate')->getData();
            /** @var array $valEndTime (e.g. array('hour' => 12, 'minute' => 17, 'second' => 26)) */
            $valEndTime = $pollProposalForm->get('endTime')->getData();

            if ($this->castArrayValuesToInt($valEndDate)) {
                $endDatetimeValue->setDate($valEndDate['year'], $valEndDate['month'], $valEndDate['day']);
                $this->pollProposal->setEndDate(true);
            } else {
                $this->pollProposal->setEndDate(false);
            }
            if ($this->castArrayValuesToInt($valEndTime)) {
                $endDatetimeValue->setTime($valEndTime['hour'], $valEndTime['minute']);
                $this->pollProposal->setEndTime(true);
            } else {
                $this->pollProposal->setEndTime(false);
            }
            $this->pollProposal->setValEndDatetime($endDatetimeValue);
        }
        return $this->pollProposal;
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