<?php

namespace AppBundle\Entity\Event;

use AppBundle\Entity\Module\ExpenseElement;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ModuleInvitation
 *
 * @ORM\Table(name="event_module_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\ModuleInvitationRepository")
 */
class ModuleInvitation
{
    /** Active les timestamps automatiques pour la creation et la mise a jour */
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_moduleinvitation_status")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var bool
     * @ORM\Column(name="creator", type="boolean")
     */
    private $creator = false;

    /**
     * @var bool
     * @ORM\Column(name="administrator", type="boolean")
     */
    private $administrator = false;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\Module", inversedBy="moduleInvitations")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\EventInvitation", inversedBy="moduleInvitations")
     * @ORM\JoinColumn(name="event_invitation_id", referencedColumnName="id")
     */
    private $eventInvitation;

    /**
     * Réponses au sondage de l'invité
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalResponse", mappedBy="moduleInvitation")
     */
    private $pollProposalResponses;

    /**
     * Dépenses créées par l'invité
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\ExpenseElement", mappedBy="creator")
     */
    private $expenseElementsCreated;

    /**
     * Dépenses payées par l'invité
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\ExpenseElement", mappedBy="payer")
     */
    private $expenseElementsPayed;

    /**
     * Dépenses conernant l'invité
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Module\ExpenseElement", mappedBy="beneficiaries")
     */
    private $expenseElementsDue;

    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->pollProposalResponses = new ArrayCollection();
        $this->expenseElementsCreated = new ArrayCollection();
        $this->expenseElementsPayed = new ArrayCollection();
        $this->expenseElementsDue = new ArrayCollection();
    }


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ModuleInvitation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return ModuleInvitation
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreator()
    {
        return $this->creator;
    }

    /**
     * @param boolean $creator
     * @return ModuleInvitation
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @param boolean $administrator
     * @return ModuleInvitation
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     * @return ModuleInvitation
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }

    /**
     * @param EventInvitation $eventInvitation
     * @return ModuleInvitation
     */
    public function setEventInvitation($eventInvitation)
    {
        $this->eventInvitation = $eventInvitation;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPollProposalResponses()
    {
        return $this->pollProposalResponses;
    }

    /**
     * Add pollProposalResponse
     * @param PollProposalResponse $pollProposalResponse
     * @return ModuleInvitation
     */
    public function addPollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses[] = $pollProposalResponse;
        $pollProposalResponse->setModuleInvitation($this);
        return $this;
    }

    /**
     * Remove pollProposalResponse
     * @param PollProposalResponse $pollProposalResponse
     */
    public function removeProposalElementResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses->removeElement($pollProposalResponse);
    }

    /**
     * @return ArrayCollection
     */
    public function getExpenseElementsCreated()
    {
        return $this->expenseElementsCreated;
    }

    /**
     * @param ExpenseElement $expenseElementCreated
     */
    public function addExpenseElementCreated(ExpenseElement $expenseElementCreated)
    {
        $this->expenseElementsCreated[] = $expenseElementCreated;
        $expenseElementCreated->setCreator($this);
    }

    /**
     * @param ExpenseElement $expenseElementCreated
     */
    public function removeExpenseElementCreated(ExpenseElement $expenseElementCreated)
    {
        $this->expenseElementsCreated->removeElement($expenseElementCreated);
    }

    /**
     * @return ArrayCollection
     */
    public function getExpenseElementsPayed()
    {
        return $this->expenseElementsPayed;
    }

    /**
     * @param ExpenseElement $expenseElementPayed
     */
    public function addExpenseElementPayed(ExpenseElement $expenseElementPayed)
    {
        $this->expenseElementsPayed[] = $expenseElementPayed;
        $expenseElementPayed->setPayer($this);
    }

    /**
     * @param ExpenseElement $expenseElementPayed
     */
    public function removeExpenseElementPayed(ExpenseElement $expenseElementPayed)
    {
        $this->expenseElementsPayed->removeElement($expenseElementPayed);
    }

    /**
     * @return ArrayCollection
     */
    public function getExpenseElementsDue()
    {
        return $this->expenseElementsDue;
    }

    /**
     * @param ExpenseElement $expenseElementDue
     */
    public function addExpenseElementDue($expenseElementDue)
    {
        $this->expenseElementsDue[] = $expenseElementDue;
    }

    /**
     * @param ExpenseElement $expenseElementDue
     */
    public function removeExpenseElementDue(ExpenseElement $expenseElementDue)
    {
        $this->expenseElementsDue->removeElement($expenseElementDue);
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Initialize the response for pollModule
     * @return bool true if responses were initialize, false if pollModule is null
     */
    public function initPollModuleResponse()
    {
        if ($this->module->getPollModule() != null) {
            /** @var PollModule $pollModule */
            $pollModule = $this->module->getPollModule();
            /** @var PollProposal $pollProposal */
            foreach ($pollModule->getPollProposals() as $pollProposal) {
                $existResponse = false;
                for ($i = 0; $i < count($this->pollProposalResponses) && !$existResponse; $i++) {
                    /** @var PollProposalResponse $response */
                    $response = $this->pollProposalResponses[$i];
                    if ($response->getPollProposal() == $pollProposal) {
                        $existResponse = true;
                    }
                }
                if (!$existResponse) {
                    $newResponse = new PollProposalResponse();
                    $pollProposal->addPollProposalResponse($newResponse);
                    $this->addPollProposalResponse($newResponse);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Retourne le nom de l'invité à afficher en fonction des données renseignées et de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableName()
    {
        return $displayableName = $this->eventInvitation->getDisplayableName();
    }
}
