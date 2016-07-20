<?php

namespace AppBundle\Entity;

use AppBundle\Entity\module\PollModule;
use AppBundle\Entity\module\PollProposal;
use AppBundle\Entity\module\PollProposalResponse;
use AppBundle\Entity\payment\Wallet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ModuleInvitation
 *
 * @ORM\Table(name="module_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleInvitationRepository")
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
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="moduleInvitations")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventInvitation", inversedBy="moduleInvitations")
     * @ORM\JoinColumn(name="event_invitation_id", referencedColumnName="id")
     */
    private $eventInvitation;

    /**
     * @var Event
     *
     * @ORM\OneToOne(targetEntity="Module", mappedBy="creator")
     */
    private $createdmodule;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\payment\Wallet", mappedBy="moduleInvitation")
     *
     * @var Wallet
     */
    private $wallet;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\module\PollProposalResponse", mappedBy="moduleInvitation")
     */
    private $pollProposalResponses;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pollProposalResponses = new ArrayCollection();
    }

    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Initialize the response for pollModule
     *
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
                    $pollProposal->addProposalResponse($newResponse);
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
        $displayableName = $this->name;
        if (empty($displayableName)) {
            $displayableName = $this->eventInvitation->getDisplayableName();
        }
        return $displayableName;
    }


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ModuleInvitation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set module
     *
     * @param \AppBundle\Entity\Module $module
     *
     * @return ModuleInvitation
     */
    public function setModule(\AppBundle\Entity\Module $module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return \AppBundle\Entity\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set eventInvitation
     *
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     *
     * @return ModuleInvitation
     */
    public function setEventInvitation(\AppBundle\Entity\EventInvitation $eventInvitation = null)
    {
        $this->eventInvitation = $eventInvitation;

        return $this;
    }

    /**
     * Get eventInvitation
     *
     * @return \AppBundle\Entity\EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }

    /**
     * Set createdModule
     *
     * @param Module $createdmodule
     * @return ModuleInvitation
     */
    public function setCreatedmodule(Module $createdmodule)
    {
        $this->createdmodule = $createdmodule;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedmodule()
    {
        return $this->createdmodule;
    }

    /**
     * Set wallet
     *
     * @param \AppBundle\Entity\payment\Wallet $wallet
     *
     * @return ModuleInvitation
     */
    public function setWallet(\AppBundle\Entity\payment\Wallet $wallet = null)
    {
        $this->wallet = $wallet;
        return $this;
    }

    /**
     * Get wallet
     *
     * @return \AppBundle\Entity\payment\Wallet
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * Add pollProposalResponse
     *
     * @param PollProposalResponse $pollProposalResponse
     *
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
     *
     * @param PollProposalResponse $pollProposalResponse
     */
    public function removeProposalElementResponse(\AppBundle\Entity\module\PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses->removeElement($pollProposalResponse);
    }

    /**
     * @return ArrayCollection
     */
    public function getPollProposalResponses()
    {
        return $this->pollProposalResponses;
    }
}
