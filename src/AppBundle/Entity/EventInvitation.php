<?php

namespace AppBundle\Entity;

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
     * @var string
     * @ORM\Column(name="status", type="string", length=64)
     */
    private $status;

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


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var AppUser
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AppUser", inversedBy="eventInvitations")
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ModuleInvitation", mappedBy="eventInvitation", cascade={"persist"} )
     */
    private $moduleInvitations;

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

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return EventInvitation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return EventInvitation
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenEdition()
    {
        return $this->tokenEdition;
    }

    /**
     * @param string $tokenEdition
     * @return EventInvitation
     */
    public function setTokenEdition($tokenEdition)
    {
        $this->tokenEdition = $tokenEdition;
        return $this;
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
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set appUser
     *
     * @param AppUser $appUser
     *
     * @return EventInvitation
     */
    public function setAppUser(AppUser $appUser = null)
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
     * @param Event $createdEvent
     *
     * @return EventInvitation
     */
    public function setCreatedEvent(Event $createdEvent = null)
    {
        $this->createdEvent = $createdEvent;

        return $this;
    }

    /**
     * Get createdEvent
     *
     * @return Event
     */
    public function getCreatedEvent()
    {
        return $this->createdEvent;
    }

    /**
     * Add moduleInvitation
     *
     * @param ModuleInvitation $moduleInvitation
     *
     * @return EventInvitation
     */
    public function addModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;
        $moduleInvitation->setEventInvitation($this);

        return $this;
    }

    /**
     * Remove moduleInvitation
     *
     * @param ModuleInvitation $moduleInvitation
     */
    public function removeModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations->removeElement($moduleInvitation);
    }

    /**
     * Get moduleInvitations
     *
     * @return ArrayCollection
     */
    public function getModuleInvitations()
    {
        return $this->moduleInvitations;
    }

    /***********************************************************************
     *                      Helpers
     ***********************************************************************/
    /**
     * Retourne le nom de l'invité à afficher en fonction des données renseignées et de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableName()
    {
        $displayableName = $this->name;
        if (empty($displayableName)) {
            if ($this->getAppUser() != null && $this->getAppUser()->getUser() != null && $this->getAppUser()->getUser()->isEnabled()) {
                $displayableName = $this->getAppUser()->getUser()->__toString();
            } else {
                $displayableName = null;
            }
        }
        return $displayableName;
    }

    /**
     * Retourne le nom de l'invité à afficher en fonction des données renseignées et de l'utilisateur associé.
     * @return string
     */
    public function getDisplayableEmail()
    {
        $displayableEmail = null;
        if ($this->getAppUser() != null && $this->getAppUser()->getUser() != null) {
            $displayableEmail = $this->getAppUser()->getUser()->getEmail();
        }
        return $displayableEmail;
    }

}
