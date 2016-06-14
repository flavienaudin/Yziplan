<?php

namespace AppBundle\Entity;

use ATUserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

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
     * @ORM\OneToOne(targetEntity="ATUserBundle\Entity\User", inversedBy="appUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var User
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EventInvitation", mappedBy="appUser")
     */
    private $eventInvitations;

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
     * Constructor
     */
    public function __construct()
    {
        $this->eventInvitations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add eventInvitation
     *
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     *
     * @return AppUser
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
