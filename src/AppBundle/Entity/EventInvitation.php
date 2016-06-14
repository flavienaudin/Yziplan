<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * EventInvitation
 *
 * @ORM\Table(name="event_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventInvitationRepository")
 */
class EventInvitation
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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/
    
    /**
     * @var ProposalElementResponse
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\module\ProposalElementResponse", mappedBy="eventInvitation")
     */
    private $proposalElementResponse;
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return EventInvitation
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->proposalElementResponse = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add proposalElementResponse
     *
     * @param \AppBundle\Entity\module\ProposalElementResponse $proposalElementResponse
     *
     * @return EventInvitation
     */
    public function addProposalElementResponse(\AppBundle\Entity\module\ProposalElementResponse $proposalElementResponse)
    {
        $this->proposalElementResponse[] = $proposalElementResponse;

        return $this;
    }

    /**
     * Remove proposalElementResponse
     *
     * @param \AppBundle\Entity\module\ProposalElementResponse $proposalElementResponse
     */
    public function removeProposalElementResponse(\AppBundle\Entity\module\ProposalElementResponse $proposalElementResponse)
    {
        $this->proposalElementResponse->removeElement($proposalElementResponse);
    }

    /**
     * Get proposalElementResponse
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposalElementResponse()
    {
        return $this->proposalElementResponse;
    }
}
