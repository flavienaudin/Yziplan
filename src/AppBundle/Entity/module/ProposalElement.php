<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\enum\ProposalElementType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ProposalElement
 *
 * @ORM\Table(name="proposal_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ProposalElementRepository")
 */
class ProposalElement
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
     * @var String
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Numéro de placement dans l'ordonancement des elements lors de l'affichage
     *
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", length=2, nullable=true)
     */
    private $order;

    /**
     * Type de la valeur de la réponse
     *
     * @var ProposalElementType
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * @var Proposal
     *
     * @ORM\ManyToOne(targetEntity="Proposal", inversedBy="proposalElements")
     * @ORM\JoinColumn(name="proposal_id", referencedColumnName="id")
     */
    private $proposal;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProposalElementResponse", mappedBy="ProposalElement")
     */
    private $proposalElementResponses;

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
        $this->proposalElementResponses = new ArrayCollection();
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProposalElement
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
     * Set order
     *
     * @param integer $order
     *
     * @return ProposalElement
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ProposalElement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set proposal
     *
     * @param \AppBundle\Entity\module\Proposal $proposal
     *
     * @return ProposalElement
     */
    public function setProposal(Proposal $proposal = null)
    {
        $this->proposal = $proposal;

        return $this;
    }

    /**
     * Get proposal
     *
     * @return \AppBundle\Entity\module\Proposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * Add proposalElementResponse
     *
     * @param ProposalElementResponse $proposalElementResponse
     *
     * @return ProposalElement
     */
    public function addProposalElementResponse(ProposalElementResponse $proposalElementResponse)
    {
        $this->proposalElementResponses[] = $proposalElementResponse;

        return $this;
    }

    /**
     * Remove proposalElementResponse
     *
     * @param ProposalElementResponse $proposalElementResponse
     */
    public function removeProposalElementResponse(ProposalElementResponse $proposalElementResponse)
    {
        $this->proposalElementResponses->removeElement($proposalElementResponse);
    }

    /**
     * Get proposalElementResponses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposalElementResponses()
    {
        return $this->proposalElementResponses;
    }
}
