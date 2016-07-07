<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\enum\PollProposalElementType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PollProposalElement
 *
 * @ORM\Table(name="poll_proposal_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollProposalElementRepository")
 */
class PollProposalElement
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
     * @ORM\Column(name="order_index", type="integer", length=2, nullable=true)
     */
    private $order;

    /**
     * Type de la valeur de la réponse
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * Contient les informations complémentaires, par exemple le placeId pour un lieu, le fuseau horaire pour une date, etc...
     *
     * @var string
     *
     * @ORM\Column(name="additional_data", type="string", length=255, nullable=true)
     */
    private $additionalData;

    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * @var PollProposal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\module\PollProposal", inversedBy="pollProposalElements")
     * @ORM\JoinColumn(name="poll_proposal_id", referencedColumnName="id")
     */
    private $pollProposal;

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
     * Set name
     *
     * @param string $name
     *
     * @return PollProposalElement
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
     * @return PollProposalElement
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
     * @return PollProposalElement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return PollProposalElementType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param string $additionalData
     * @return PollProposalElement
     */
    public function setAdditionalData($additionalData = null)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    /**
     * Set pollProposal
     *
     * @param PollProposal $pollProposal
     *
     * @return PollProposalElement
     */
    public function setPollProposal(PollProposal $pollProposal)
    {
        $this->pollProposal = $pollProposal;

        return $this;
    }

    /**
     * Get pollProposal
     *
     * @return PollProposal
     */
    public function getPollProposal()
    {
        return $this->pollProposal;
    }
}
