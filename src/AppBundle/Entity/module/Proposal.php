<?php

namespace AppBundle\Entity\module;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Proposal
 *
 * @ORM\Table(name="proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ProposalRepository")
 */
class Proposal
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

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/
    
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Proposal
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
     * @return Proposal
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
