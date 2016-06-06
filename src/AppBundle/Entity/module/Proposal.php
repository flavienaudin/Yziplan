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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProposalElement", mappedBy="proposal")
     */
    private $proposalElements;
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->proposalElements = new \Doctrine\Common\Collections\ArrayCollection();
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
