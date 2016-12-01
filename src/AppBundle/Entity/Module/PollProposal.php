<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Proposal
 *
 * @ORM\Table(name="module_poll_proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollProposalRepository")
 */
class PollProposal
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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * ModuleInvitation de l'invité ayant ajouté la proposition
     *
     * @var ModuleInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="pollProposalsCreated")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var PollModule
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollModule", inversedBy="pollProposals")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     */
    private $pollModule;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalElement", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalElements;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalResponse", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalResponses;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->pollProposalElements = new ArrayCollection();
        $this->pollProposalResponses = new ArrayCollection();
    }


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PollProposal
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return PollProposal
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return ModuleInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param ModuleInvitation $creator
     * @return PollProposal
     */
    public function setCreator(ModuleInvitation $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * @param PollModule $pollModule
     * @return PollProposal
     */
    public function setPollModule(PollModule $pollModule)
    {
        $this->pollModule = $pollModule;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPollProposalElements()
    {
        return $this->pollProposalElements;
    }

    /**
     * Add proposalElement
     * @param PollProposalElement $pollProposalElement
     * @return PollProposal
     */
    public function addPollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements[] = $pollProposalElement;
        $pollProposalElement->setPollProposal($this);

        return $this;
    }

    /**
     * Remove pollProposalElement
     * @param PollProposalElement $pollProposalElement
     */
    public function removePollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements->removeElement($pollProposalElement);
    }

    /**
     * Get pollProposalResponses
     * @return ArrayCollection
     */
    public function getPollProposalResponses()
    {
        return $this->pollProposalResponses;
    }

    /**
     * @param PollProposalResponse $pollProposalResponse
     * @return PollProposal
     */
    public function addPollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses[] = $pollProposalResponse;
        $pollProposalResponse->setPollProposal($this);
        return $this;
    }

    /**
     * Remove pollProposalResponse
     * @param PollProposalResponse $pollProposalResponse
     */
    public function removePollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses->removeElement($pollProposalResponse);
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Create all PollProposalElement for this proposal , based on the PollModule configuration
     * Do not add this PollProposal to the PollModule to avoid displaying non finished proposal
     * @param PollModule $pollModule
     */
    public function initializeWithPollModule(PollModule $pollModule)
    {
        foreach ($pollModule->getOrderedPollElements() as $pollElement) {
            $ppElt = new PollProposalElement();
            $ppElt->setPollElement($pollElement);
            $this->addPollProposalElement($ppElt);
        }
    }

    /**
     * Return PollProposalReponses concerning the PollProposal, of the ModuleInvitation
     * @param $moduleInvitations
     */
    public function getPollProposalResponsesOfModuleInvitations($moduleInvitations)
    {
        $pollProposalResponses = array();
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($moduleInvitations as $moduleInvitation) {
            $miPollProposalResponse = $moduleInvitation->getPollProposalResponses();
            $criteria = Criteria::create()->where(Criteria::expr()->eq("pollProposal", $this));
            $criteria->setMaxResults(1);
            $response = $miPollProposalResponse->matching($criteria);
            $pollProposalResponses[] = $response[0];

        }
        return $pollProposalResponses;
    }
}
