<?php

namespace AppBundle\Entity;

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
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
}
