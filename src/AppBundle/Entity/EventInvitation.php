<?php

namespace AppBundle\Entity;

use AppBundle\Entity\module\ProposalElementResponse;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

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
     * @var AppUser
     *
     * @ORM\ManyToOne(targetEntity="AppUser", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="app_user_id", referencedColumnName="id")
     */
    private $appUser;

    /**
     * @var Event
     *
     * @ORM\OneToOne(targetEntity="Event", mappedBy="creator")
     */
    private $createdEvent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ModuleInvitation", mappedBy="eventInvitation")
     */
    private $moduleInvitations;
    
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

    /**
     * Set appUser
     *
     * @param \AppBundle\Entity\AppUser $appUser
     *
     * @return EventInvitation
     */
    public function setAppUser(\AppBundle\Entity\AppUser $appUser = null)
    {
        $this->appUser = $appUser;

        return $this;
    }

    /**
     * Get appUser
     *
     * @return \AppBundle\Entity\AppUser
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

    /**
     * Set createdEvent
     *
     * @param \AppBundle\Entity\Event $createdEvent
     *
     * @return EventInvitation
     */
    public function setCreatedEvent(\AppBundle\Entity\Event $createdEvent = null)
    {
        $this->createdEvent = $createdEvent;

        return $this;
    }

    /**
     * Get createdEvent
     *
     * @return \AppBundle\Entity\Event
     */
    public function getCreatedEvent()
    {
        return $this->createdEvent;
    }

    /**
     * Add moduleInvitation
     *
     * @param \AppBundle\Entity\ModuleInvitation $moduleInvitation
     *
     * @return EventInvitation
     */
    public function addModuleInvitation(\AppBundle\Entity\ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;

        return $this;
    }

    /**
     * Remove moduleInvitation
     *
     * @param \AppBundle\Entity\ModuleInvitation $moduleInvitation
     */
    public function removeModuleInvitation(\AppBundle\Entity\ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations->removeElement($moduleInvitation);
    }

    /**
     * Get moduleInvitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModuleInvitations()
    {
        return $this->moduleInvitations;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    
}
