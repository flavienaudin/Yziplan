<?php

namespace AppBundle\Entity\Module;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PollProposalElement
 *
 * @ORM\Table(name="module_poll_proposal_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollProposalElementRepository")
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
     * Contient la valeur si le PollElement correspondant est de type
     *
     * @var string     *
     * @ORM\Column(name="val_string", type="string", length=511, nullable=true)
     */
    private $valString;

    /**
     * Contient la valeur si le type == PollElementType::INTEGER
     *
     * @var integer
     *
     * @ORM\Column(name="val_integer", type="integer", nullable=true)
     */
    private $valInteger;

    /**
     * Contient la valeur si le type == PollElementType::DATE_TIME
     *
     * @var \DateTime
     *
     * @ORM\Column(name="val_datetime", type="datetime", nullable=true)
     */
    private $valDatetime;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var PollProposal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollProposal", inversedBy="pollProposalElements")
     * @ORM\JoinColumn(name="poll_proposal_id", referencedColumnName="id")
     */
    private $pollProposal;

    /**
     * @var PollElement
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollElement", inversedBy="pollProposalElements")
     * @ORM\JoinColumn(name="poll_element_id", referencedColumnName="id")
     */
    private $pollElement;


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

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
     * @return PollProposal
     */
    public function getPollProposal()
    {
        return $this->pollProposal;
    }

    /**
     * @param PollProposal $pollProposal
     * @return PollProposalElement
     */
    public function setPollProposal($pollProposal)
    {
        $this->pollProposal = $pollProposal;
        return $this;
    }

    /**
     * @return PollElement
     */
    public function getPollElement()
    {
        return $this->pollElement;
    }

    /**
     * @param PollElement $pollElement
     * @return PollProposalElement
     */
    public function setPollElement($pollElement)
    {
        $this->pollElement = $pollElement;
        return $this;
    }
}