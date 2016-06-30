<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\Module as Module;
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
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Module", inversedBy="pollModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     *
     * @var Module
     */
    private $module;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="pollModule", cascade={"persist"})
     */
    private $proposals;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->proposals = new ArrayCollection();
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
     * @param Proposal $proposal
     *
     * @return PollModule
     */
    public function addProposal(Proposal $proposal)
    {
        $this->proposals[] = $proposal;
        $proposal->setPollModule($this);

        return $this;
    }

    /**
     * Remove proposal
     *
     * @param Proposal $proposal
     */
    public function removeProposal(Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);
    }

    /**
     * Get proposals
     *
     * @return ArrayCollection
     */
    public function getProposals()
    {
        return $this->proposals;
    }
}