<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PollProposalResponse
 *
 * @ORM\Table(name="module_poll_proposal_response")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollProposalResponseRepository")
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
     * @var String Peut être une chaine de caractère (key), un nombre (note, quantité)
     * @ORM\Column(name="answer", type="string", nullable=true)
     */
    private $answer;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var PollProposal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollProposal", inversedBy="pollProposalResponses")
     * @ORM\JoinColumn(name="poll_proposal_id", referencedColumnName="id")
     */
    private $pollProposal;

    /**
     * @var ModuleInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="pollProposalResponses")
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
     * @param string $answer
     *
     * @return PollProposalResponse
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set pollProposal
     *
     * @param PollProposal $pollProposal
     * @return PollProposalResponse
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
