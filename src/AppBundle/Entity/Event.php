<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=128, unique=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="token_edition", type="string", length=128, unique=true)
     */
    private $tokenEdition;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(name="response_deadline", type="datetime", unique=false, nullable=true)
     */
    private $responseDeadline;

    /**
     * If "true" then only guest with invitation can answer. No eventInivtaiton creation when displaying the event
     * @var boolean
     *
     * @ORM\Column(name="invitation_only", type="boolean")
     */
    private $invitationOnly = true;

    /**
     * * If "true" then guests can send invitations to others.
     * @var boolean
     * @ORM\Column(name="guests_can_invite", type="boolean")
     */
    private $guestsCanInvite = false;

    /**
     * * If "true" then guests can add module
     * @var boolean
     * @ORM\Column(name="guests_can_add_module", type="boolean")
     */
    private $guestsCanAddModule = false;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var EventInvitation
     *
     * @ORM\OneToOne(targetEntity="EventInvitation", inversedBy="createdEvent", cascade={"persist"})
     * @ORM\JoinColumn(name="creator_event_invitation_id", referencedColumnName="id", nullable=true)
     */
    private $creator;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Module", mappedBy="event", cascade={"persist"})
     * @ORM\OrderBy({"orderIndex" = "ASC"})
     */
    private $modules;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventInvitation", mappedBy="event", cascade={"persist"})
     */
    private $eventInvitations;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->eventInvitations = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Event
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
     * @return Event
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

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Event
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set tokenEdition
     *
     * @param string $tokenEdition
     *
     * @return Event
     */
    public function setTokenEdition($tokenEdition)
    {
        $this->tokenEdition = $tokenEdition;

        return $this;
    }

    /**
     * Get tokenEdition
     *
     * @return string
     */
    public function getTokenEdition()
    {
        return $this->tokenEdition;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Event
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set responseDeadline
     *
     * @param \DateTime $responseDeadline
     *
     * @return Event
     */
    public function setResponseDeadline($responseDeadline)
    {
        $this->responseDeadline = $responseDeadline;

        return $this;
    }

    /**
     * Get responseDeadline
     *
     * @return \DateTime
     */
    public function getResponseDeadline()
    {
        return $this->responseDeadline;
    }

    /**
     * @return boolean
     */
    public function isInvitationOnly()
    {
        return $this->invitationOnly;
    }

    /**
     * @param boolean $invitationOnly
     * @return Event
     */
    public function setInvitationOnly($invitationOnly)
    {
        $this->invitationOnly = $invitationOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isGuestsCanInvite()
    {
        return $this->guestsCanInvite;
    }

    /**
     * @param boolean $guestsCanInvite
     * @return Event
     */
    public function setGuestsCanInvite($guestsCanInvite)
    {
        $this->guestsCanInvite = $guestsCanInvite;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isGuestsCanAddModule()
    {
        return $this->guestsCanAddModule;
    }

    /**
     * @param boolean $guestsCanAddModule
     * @return Event
     */
    public function setGuestsCanAddModule($guestsCanAddModule)
    {
        $this->guestsCanAddModule = $guestsCanAddModule;
        return $this;
    }

    /**
     * Set creator
     *
     * @param EventInvitation $creator
     *
     * @return Event
     */
    public function setCreator(EventInvitation $creator = null)
    {
        $this->creator = $creator;
        if ($creator != null) {
            $creator->setCreatedEvent($this);
        }

        return $this;
    }

    /**
     * Get creator
     *
     * @return EventInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }


    /**
     * Add module
     *
     * @param Module $module
     *
     * @return Event
     */
    public function addModule(Module $module)
    {
        $this->modules[] = $module;
        $module->setEvent($this);

        return $this;
    }

    /**
     * Remove module
     *
     * @param Module $module
     */
    public function removeModule(Module $module)
    {
        $this->modules->removeElement($module);
    }

    /**
     * Get modules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Add eventInvitation
     *
     * @param EventInvitation $eventInvitation
     *
     * @return Event
     */
    public function addEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;
        $eventInvitation->setEvent($this);

        return $this;
    }

    /**
     * Remove eventInvitation
     *
     * @param EventInvitation $eventInvitation
     */
    public function removeEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    /**
     * Get eventInvitations
     *
     * @return ArrayCollection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }
}
