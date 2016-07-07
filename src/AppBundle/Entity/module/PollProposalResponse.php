<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\ModuleInvitation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PollProposalResponse
 *
 * @ORM\Table(name="poll_proposal_response")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollProposalResponseRepository")
 */
class PollProposalResponse
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
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var PollProposal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\module\PollProposal", inversedBy="pollProposalResponses")
     * @ORM\JoinColumn(name="poll_proposal_id", referencedColumnName="id")
     */
    private $pollProposal;

    /**
     * @var ModuleInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModuleInvitation", inversedBy="pollProposalResponse")
     * @ORM\JoinColumn(name="module_invitation_id", referencedColumnName="id")
     */
    private $moduleInvitation;

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
     * Set value
     *
     * @param string $value
     *
     * @return PollProposalResponse
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set pollProposal
     *
     * @param PollProposal $pollProposal
     *
     * @return PollProposalResponse
     */
    public function setPollProposal(PollProposal $pollProposal)
    {
        $this->pollProposal = $pollProposal;

        return $this;
    }

    /**
     * Get proposalElement
     *
     * @return \AppBundle\Entity\module\PollProposalElement
     */
    public function getPollProposal()
    {
        return $this->pollProposal;
    }

    /**
     * @return ModuleInvitation
     */
    public function getModuleInvitation()
    {
        return $this->moduleInvitation;
    }

    /**
     * @param ModuleInvitation $moduleInvitation
     * @return PollProposalResponse
     */
    public function setModuleInvitation($moduleInvitation)
    {
        $this->moduleInvitation = $moduleInvitation;
        return $this;
    }
}
