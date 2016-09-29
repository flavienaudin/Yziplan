<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\Module;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(name="sorting_type", type="enum_pollmodule_sortingtype")
     */
    private $sortingType;


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
        $this->pollElements = $pollElement;
        $pollElement->setPollModule($this);

        return $this;
    }

    /**
     * @param PollElement $pollElement
     */
    public function removePollElement(PollElement $pollElement)
    {
        $this->pollElements->removeElement($pollElement);
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
        /** @var PollProposal $pollProposal */
        foreach ($this->pollProposals as $pollProposal) {
            if ($pollProposal->getId() == $pollProposalId) {
                return $pollProposal;
            }
        }
        return null;
    }
}
