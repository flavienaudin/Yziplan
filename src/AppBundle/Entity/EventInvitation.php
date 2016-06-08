<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventInvitation
 *
 * @ORM\Table(name="event_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventInvitationRepository")
 */
class EventInvitation
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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

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
}
