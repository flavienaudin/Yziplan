<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\Module as Module;
use Doctrine\ORM\Mapping as ORM;

/**
 * PollModule
 *
 * @ORM\Table(name="poll_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollModuleRepository")
 */
class PollModule
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Module", inversedBy="pollModules")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     *
     * @var Module
     */
    private $module;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="pollModule")
     */
    private $proposals;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->proposals = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \AppBundle\Entity\Module $module
     *
     * @return PollModule
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
     * Add proposal
     *
     * @param \AppBundle\Entity\module\Proposal $proposal
     *
     * @return PollModule
     */
    public function addProposal(\AppBundle\Entity\module\Proposal $proposal)
    {
        $this->proposals[] = $proposal;

        return $this;
    }

    /**
     * Remove proposal
     *
     * @param \AppBundle\Entity\module\Proposal $proposal
     */
    public function removeProposal(\AppBundle\Entity\module\Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);
    }

    /**
     * Get proposals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposals()
    {
        return $this->proposals;
    }
}
