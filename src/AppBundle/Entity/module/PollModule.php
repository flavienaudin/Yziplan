<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * PollModule
 *
 * @ORM\Table(name="poll_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollModuleRepository")
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Module", inversedBy="pollModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     *
     * @var Module
     */
    private $module;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\module\PollProposal", mappedBy="pollModule", cascade={"persist"})
     */
    private $pollProposals;

    /**
     * @var string
     *
     * @ORM\Column(name="sorting_type", type="string", length=127)
     */
    private $sortingType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pollProposals = new ArrayCollection();
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
    public function getSortingType()
    {
        return $this->sortingType;
    }

    /**
     * @param string $sortingType
     * @return PollModule
     */
    public function setSortingType($sortingType)
    {
        $this->sortingType = $sortingType;
        return $this;
    }


    /**
     * Set module
     *
     * @param Module $module
     *
     * @return PollModule
     */
    public function setModule(Module $module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
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
     * Remove proposal
     *
     * @param PollProposal $pollProposal
     */
    public function removePollProposal(PollProposal $pollProposal)
    {
        //TODO set Status
        $this->pollProposals->removeElement($pollProposal);
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
     * Get the proposal by its Id
     *
     * @param $pollProposalId
     * @return PollProposal|null
     */
    public function getPollProposalById($pollProposalId)
    {
        /** @var PollProposal $pollProposal */
        foreach ($this->pollProposals as $pollProposal) {
            if ($pollProposal->getId() == $pollProposalId) {
                return $pollProposal;
            }
        }
        return null;
    }
}
