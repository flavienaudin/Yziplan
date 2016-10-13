<?php

namespace AppBundle\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Contact
 *
 * @ORM\Table(name="user_contact")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\ContactRepository")
 */
class Contact
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
     * @ORM\Column(name="status", type="enum_contact_status")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="enum_gender", nullable=true)
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=255, nullable=true)
     */
    private $nationality;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ApplicationUser Utilisateur ayant créé le contact (Relation Bidirectionnelle)
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", inversedBy="contacts" )
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * @var ArrayCollection of ApplicationUser association entre un contact et les utilisateurs correspondants (Relation unidirectionnelle)
     * Il se peut qu'une personne physique (représentée par un Contact) aient plusieurs comptes, d'où l'association ManyToMany
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User\ApplicationUser" )
     * @ORM\JoinTable(name="user_contacts_linked")
     */
    private $linkeds;

    /**
     * @var ArrayCollection of ContactGroup
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User\ContactGroup", inversedBy="contacts")
     * @ORM\JoinTable(name="user_contacts_groups")
     */
    private $groups;

    /**
     * @var ArrayCollection of ContactEmail
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User\ContactEmail", mappedBy="contact")
     */
    private $contactEmails;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->linkeds = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->contactEmails = new ArrayCollection();
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
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Contact
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return Contact
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     * @return Contact
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     * @return Contact
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @return ApplicationUser
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param ApplicationUser $owner
     * @return Contact
     */
    public function setOwner(ApplicationUser $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLinkeds()
    {
        return $this->linkeds;
    }

    /**
     * @param ApplicationUser $linked
     * @return Contact
     */
    public function addLinked(ApplicationUser $linked)
    {
        $this->linkeds[] = $linked;
        return $this;
    }

    /**
     * @param ApplicationUser $linked
     * @return Contact
     */
    public function removeLinked(ApplicationUser $linked)
    {
        $this->linkeds->removeElement($linked);
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
     * @param ContactGroup $contactGroup
     * @return Contact
     */
    public function addGroup(ContactGroup $contactGroup)
    {
        $this->groups[] = $contactGroup;
        return $this;
    }

    /**
     * @param ArrayCollection $contactGroup
     * @return Contact
     */
    public function removeGroup(ContactGroup $contactGroup)
    {
        $this->groups->removeElement($contactGroup);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getContactEmails()
    {
        return $this->contactEmails;
    }

    /**
     * @param ContactEmail $contactEmail
     * @return Contact
     */
    public function addContactEmail(ContactEmail $contactEmail)
    {
        $this->contactEmails[] = $contactEmail;
        return $this;
    }

    /**
     * @param ContactEmail $contactEmail
     * @return Contact
     */
    public function removeContactEmail(ContactEmail $contactEmail)
    {
        $this->contactEmails->removeElement($contactEmail);
        return $this;
    }
}

