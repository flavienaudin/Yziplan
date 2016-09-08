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
     * @ORM\Column(name="order_index", type="integer", length=3, nullable=true)
     */
    private $orderIndex;

    /**
     * Type de la valeur de l'élément (string, booléan, date...)
     *
     * @var string Cf. AppBundle/enum/PollProposalElementType
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * Contient la valeur si le type == PollProposalElementType::STRING
     *
     * @var string
     *
     * @ORM\Column(name="val_string", type="string", length=511, nullable=true)
     */
    private $valString;

    /**
     * Contient la valeur si le type == PollProposalElementType::INTEGER
     *
     * @var integer
     *
     * @ORM\Column(name="val_integer", type="integer", nullable=true)
     */
    private $valInteger;

    /**
     * Contient la valeur si le type == PollProposalElementType::DATE_TIME
     *
     * @var \DateTime
     *
     * @ORM\Column(name="val_datetime", type="datetime", nullable=true)
     */
    private $valDatetime;

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
     * Set orderIndex
     *
     * @param integer $orderIndex
     *
     * @return PollProposalElement
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
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
     * @return string Cf. PollProposalElementType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValString()
    {
        return $this->valString;
    }

    /**
     * @param string $valString
     * @return PollProposalElement
     */
    public function setValString($valString)
    {
        $this->valString = $valString;
        return $this;
    }

    /**
     * @return int
     */
    public function getValInteger()
    {
        return $this->valInteger;
    }

    /**
     * @param int $valInteger
     * @return PollProposalElement
     */
    public function setValInteger($valInteger)
    {
        $this->valInteger = $valInteger;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValDatetime()
    {
        return $this->valDatetime;
    }

    /**
     * @param \DateTime $valDatetime
     * @return PollProposalElement
     */
    public function setValDatetime($valDatetime)
    {
        $this->valDatetime = $valDatetime;
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
