<?php

namespace AppBundle\Entity;

use ATUserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
{
    const ETAT_EN_ORGANISATION="en-organisation";
    const ETAT_EN_ATTENTE_VALIDATION="en-attente-validation";
    const ETAT_VALIDE="valide";
    const ETAT_ARCHIVE="archive";
    const ETAT_DEPROGRAMME="deprogramme";
    
    /** @var array Liste des états possibles pour un événement */
    private $statusList=array(self::ETAT_EN_ORGANISATION, self::ETAT_EN_ATTENTE_VALIDATION,
        self::ETAT_VALIDE, self::ETAT_ARCHIVE, self::ETAT_DEPROGRAMME);

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
     * @ORM\Column(name="name", type="string", length=255)
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
     * @ORM\Column(name="response_end_date", type="datetime", unique=false, nullable=true)
     */
    private $ResponseEndDate;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var UserInfos
     *
     * @ORM\ManyToOne(targetEntity="AppUser", inversedBy="createdEvent")
     * @ORM\JoinColumn(name="user_info_id", referencedColumnName="id")
     */
    private $creator;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Module", mappedBy="event")
     */
    private $modules;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventInvitation", mappedBy="event")
     */
    private $eventInvitations;

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
     * Constructor
     */
    public function __construct()
    {
        $this->modules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventInvitations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add module
     *
     * @param \AppBundle\Entity\Module $module
     *
     * @return Event
     */
    public function addModule(\AppBundle\Entity\Module $module)
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Remove module
     *
     * @param \AppBundle\Entity\Module $module
     */
    public function removeModule(\AppBundle\Entity\Module $module)
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
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     *
     * @return Event
     */
    public function addEventInvitation(\AppBundle\Entity\EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;

        return $this;
    }

    /**
     * Remove eventInvitation
     *
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     */
    public function removeEventInvitation(\AppBundle\Entity\EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    /**
     * Get eventInvitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
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
     * Set responseEndDate
     *
     * @param \DateTime $responseEndDate
     *
     * @return Event
     */
    public function setResponseEndDate($responseEndDate)
    {
        $this->ResponseEndDate = $responseEndDate;

        return $this;
    }

    /**
     * Get responseEndDate
     *
     * @return \DateTime
     */
    public function getResponseEndDate()
    {
        return $this->ResponseEndDate;
    }
    

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\AppUser $creator
     *
     * @return Event
     */
    public function setCreator(\AppBundle\Entity\AppUser $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\AppUser
     */
    public function getCreator()
    {
        return $this->creator;
    }
}
