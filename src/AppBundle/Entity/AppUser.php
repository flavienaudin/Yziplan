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
     * Reference l'ID du user mangopay si celui-ci existe
     * Peut-être null si le user mangopay n'est pas encore créé.
     *
     * @var string
     *
     * @ORM\Column(name="mangopay_user_id", type="string", length=255, nullable=true)
     */
    private $mangoPayUserId;

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
     * @param User $user
     *
     * @return AppUser
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
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
        $this->eventInvitations = new ArrayCollection();
    }

    /**
     * Add eventInvitation
     *
     * @param EventInvitation $eventInvitation
     *
     * @return AppUser
     */
    public function addEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;
        $eventInvitation->setAppUser($this);

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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }

    /**
     * Set mangoPayUserId
     *
     * @param string $mangoPayUserId
     *
     * @return AppUser
     */
    public function setMangoPayUserId($mangoPayUserId)
    {
        $this->mangoPayUserId = $mangoPayUserId;

        return $this;
    }

    /**
     * Get mangoPayUserId
     *
     * @return string
     */
    public function getMangoPayUserId()
    {
        return $this->mangoPayUserId;
    }
}
