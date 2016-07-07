<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\EventInvitation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Proposal
 *
 * @ORM\Table(name="poll_proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollProposalRepository")
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * Personne ayant ajouté la depense
     *
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventInvitation")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     *
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\module\PollModule", inversedBy="pollProposals")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     *
     * @var PollModule
     */
    private $pollModule;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\module\PollProposalElement", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalElements;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\module\PollProposalResponse", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalResponses;

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pollProposalElements = new ArrayCollection();
    }

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
     * @return EventInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param EventInvitation $creator
     * @return PollProposal
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Set pollModule
     *
     * @param PollModule $pollModule
     *
     * @return PollProposal
     */
    public function setPollModule(PollModule $pollModule)
    {
        $this->pollModule = $pollModule;

        return $this;
    }

    /**
     * Get pollModule
     *
     * @return PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * Add proposalElement
     *
     * @param PollProposalElement $pollProposalElement
     *
     * @return PollProposal
     */
    public function addProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements[] = $pollProposalElement;
        $pollProposalElement->setPollProposal($this);

        return $this;
    }

    /**
     * Remove pollProposalElement
     *
     * @param PollProposalElement $pollProposalElement
     */
    public function removeProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements->removeElement($pollProposalElement);
    }

    /**
     * Get pollProposalElements
     *
     * @return ArrayCollection
     */
    public function getPollProposalElements()
    {
        return $this->pollProposalElements;
    }

    /**
     * Add pollProposalResponse
     *
     * @param PollProposalResponse $pollProposalResponse
     *
     * @return PollProposalElement
     */
    public function addProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses[] = $pollProposalResponse;
        $pollProposalResponse->setPollProposal($this);

        return $this;
    }

    /**
     * Remove pollProposalResponse
     *
     * @param PollProposalResponse $pollProposalResponse
     */
    public function removePollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses->removeElement($pollProposalResponse);
    }

    /**
     * Get pollProposalResponses
     *
     * @return ArrayCollection
     */
    public function getPollProposalResponses()
    {
        return $this->pollProposalResponses;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PollProposal
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
     * Set description
     *
     * @param string $description
     *
     * @return PollProposal
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
