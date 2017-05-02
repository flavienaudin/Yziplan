<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * PollModule
 *
 * @ORM\Table(name="module_poll_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollModuleRepository")
 */
class PollModule
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
     * @ORM\Column(name="voting_type", type="enum_pollmodule_votingtype")
     */
    private $votingType;

    /**
     * Contient le type de module, utile pour les module pré-défini qui ont des traitements spécifiques, null sinon
     *
     * @var string
     *
     * @ORM\Column(name="poll_module_type", type="enum_pollmodule_type", nullable=true)
     */
    private $type;

    /**
     * If "true" then guests can add poll proposal
     * @var boolean
     * @ORM\Column(name="guests_can_add_proposal", type="boolean")
     */
    private $guestsCanAddProposal = true;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\Module", inversedBy="pollModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposal", mappedBy="pollModule", cascade={"persist"})
     */
    private $pollProposals;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollElement", mappedBy="pollModule", cascade={"persist"})
     */
    private $pollElements;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->pollProposals = new ArrayCollection();
        $this->pollElements = new ArrayCollection();
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
    public function getVotingType()
    {
        return $this->votingType;
    }

    /**
     * @param string $votingType
     * @return PollModule
     */
    public function setVotingType($votingType)
    {
        $this->votingType = $votingType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGuestsCanAddProposal()
    {
        return $this->guestsCanAddProposal;
    }

    /**
     * @param bool $guestsCanAddProposal
     * @return PollModule
     */
    public function setGuestsCanAddProposal($guestsCanAddProposal)
    {
        $this->guestsCanAddProposal = $guestsCanAddProposal;
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
     * @return PollModule
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get proposals
     *
     * @return ArrayCollection
     */
    public function getPollProposals()
    {
        return $this->pollProposals;
    }

    /**
     * Add proposal
     *
     * @param PollProposal $pollProposal
     *
     * @return PollModule
     */
    public function addPollProposal(PollProposal $pollProposal)
    {
        $this->pollProposals[] = $pollProposal;
        $pollProposal->setPollModule($this);

        return $this;
    }

    /**
     * TODO set Status
     * Remove proposal
     * @param PollProposal $pollProposal
     */
    public function removePollProposal(PollProposal $pollProposal)
    {
        $this->pollProposals->removeElement($pollProposal);
    }

    /**
     * @return ArrayCollection
     */
    public function getPollElements()
    {
        return $this->pollElements;
    }

    /**
     * @param PollElement $pollElement
     * @return PollModule
     */
    public function addPollElement(PollElement $pollElement)
    {
        $this->pollElements[] = $pollElement;
        $pollElement->setPollModule($this);

        return $this;
    }

    /**
     * @param ArrayCollection $pollElements
     * @return PollModule
     */
    public function addPollElements(ArrayCollection $pollElements)
    {
        foreach ($pollElements as $pollElement) {
            $this->addPollElement($pollElement);
        }
        return $this;
    }

    /**
     * @param PollElement $pollElement
     */
    public function removePollElement(PollElement $pollElement)
    {
        $this->pollElements->removeElement($pollElement);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Get the proposal by its Id
     *
     * @param $pollProposalId
     * @return PollProposal|null
     */
    public function getPollProposalById($pollProposalId)
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->eq('id', $pollProposalId)
        );
        return $this->pollProposals->matching($criteria)->get(0);
    }

    public function getValidPollProposal()
    {
        //if not pollProposal.deleted and pollProposal.id is not null
        $criteria = Criteria::create()->where(
            Criteria::expr()->andX(
                Criteria::expr()->eq("deleted", false),
                Criteria::expr()->neq("id", null)
            )
        );
        return $this->pollProposals->matching($criteria);
    }

    /**
     * @return ArrayCollection
     */
    public function getOrderedPollElements()
    {
        $criteria = Criteria::create()
            ->orderBy(["orderIndex" => Criteria::ASC]);
        return $this->pollElements->matching($criteria);
    }

}
