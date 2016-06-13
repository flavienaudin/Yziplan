<?php

namespace AppBundle\Entity;

use ATUserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppUser
 * 
 * Fait le lien entre l'utilisateur et les données applicatives.
 *
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AppUserRepository")
 */
class AppUser
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
     * @ORM\OneToOne(targetEntity="\ATUserBundle\Entity\User", inversedBy="appUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var User
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="creator")
     */
    private $createdEvent;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdEvent = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \ATUserBundle\Entity\User $user
     *
     * @return AppUser
     */
    public function setUser(\ATUserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \ATUserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add createdEvent
     *
     * @param \AppBundle\Entity\Event $createdEvent
     *
     * @return AppUser
     */
    public function addCreatedEvent(\AppBundle\Entity\Event $createdEvent)
    {
        $this->createdEvent[] = $createdEvent;

        return $this;
    }

    /**
     * Remove createdEvent
     *
     * @param \AppBundle\Entity\Event $createdEvent
     */
    public function removeCreatedEvent(\AppBundle\Entity\Event $createdEvent)
    {
        $this->createdEvent->removeElement($createdEvent);
    }

    /**
     * Get createdEvent
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreatedEvent()
    {
        return $this->createdEvent;
    }
}
