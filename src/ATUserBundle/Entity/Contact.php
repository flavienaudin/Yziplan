<?php

namespace ATUserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Contact
 *
 * @ORM\Table(name="contact")
 * @ORM\Entity(repositoryClass="ATUserBundle\Repository\ContactRepository")
 */
class Contact
{
    /** Active les timestamps automatiques pour la creation et la mise a jour */
    use TimestampableEntity;

    const STATUS_SUGGESTED = "contact.status.suggested";
    const STATUS_VALID = "contact.status.valid";
    const STATUS_DELETED = "contact.status.deleted";

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
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var User Utilisateur ayant pour contact (Relation Bidirectionnelle)
     *
     * @ORM\ManyToOne(targetEntity="ATUserBundle\Entity\User", inversedBy="contacts" )
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * @var User Utilisateur qui est le contact (Relation unidirectionnelle)
     *
     * @ORM\ManyToOne(targetEntity="ATUserBundle\Entity\User")
     * @ORM\JoinColumn(name="linked_id", referencedColumnName="id", nullable=false)
     */
    private $linked;

    /**
     * @var ArrayCollection of ContactGroup
     *
     * @ORM\ManyToMany(targetEntity="ATUserBundle\Entity\ContactGroup", inversedBy="contacts")
     * @ORM\JoinTable(name="contact_contactgroup")
     */
    private $groups;


    /** constructor */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }


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
     * Set status
     *
     * @param string $status
     *
     * @return Contact
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
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return Contact
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return User
     */
    public function getLinked()
    {
        return $this->linked;
    }

    /**
     * @param User $linked
     * @return Contact
     */
    public function setLinked($linked)
    {
        $this->linked = $linked;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add group
     *
     * @param ContactGroup $group
     *
     * @return Contact
     */
    public function addGroup(ContactGroup $group)
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Remove group
     *
     * @param ContactGroup $groupe
     */
    public function removeGroup(ContactGroup $groupe)
    {
        $this->groups->removeElement($groupe);
    }
}

