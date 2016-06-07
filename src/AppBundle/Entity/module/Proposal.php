<?php

namespace AppBundle\Entity\module;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proposal
 *
 * @ORM\Table(name="proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ProposalRepository")
 */
class Proposal
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
     * @ORM\ManyToOne(targetEntity="PollModule", inversedBy="proposals")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     *
     * @var PollModule
     */
    private $pollModule;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProposalElement", mappedBy="proposal")
     */
    private $proposalElements;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->proposalElements = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set pollModule
     *
     * @param \AppBundle\Entity\module\PollModule $pollModule
     *
     * @return Proposal
     */
    public function setPollModule(\AppBundle\Entity\module\PollModule $pollModule = null)
    {
        $this->pollModule = $pollModule;

        return $this;
    }

    /**
     * Get pollModule
     *
     * @return \AppBundle\Entity\module\PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * Add proposalElement
     *
     * @param \AppBundle\Entity\module\ProposalElement $proposalElement
     *
     * @return Proposal
     */
    public function addProposalElement(\AppBundle\Entity\module\ProposalElement $proposalElement)
    {
        $this->proposalElements[] = $proposalElement;

        return $this;
    }

    /**
     * Remove proposalElement
     *
     * @param \AppBundle\Entity\module\ProposalElement $proposalElement
     */
    public function removeProposalElement(\AppBundle\Entity\module\ProposalElement $proposalElement)
    {
        $this->proposalElements->removeElement($proposalElement);
    }

    /**
     * Get proposalElements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposalElements()
    {
        return $this->proposalElements;
    }
}
