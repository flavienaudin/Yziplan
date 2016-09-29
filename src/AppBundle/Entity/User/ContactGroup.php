<?php

namespace AppBundle\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContactGroup
 *
 * @ORM\Table(name="user_contact_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\ContactGroupRepository")
 */
class ContactGroup
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_first_name", type="boolean")
     */
    private $displayFirstName;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_last_name", type="boolean")
     */
    private $displayLastName;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_gender", type="boolean")
     */
    private $displayGender;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_birthday", type="boolean")
     */
    private $displayBirthday;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_nationality", type="boolean")
     */
    private $displayNationality;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_living_city", type="boolean")
     */
    private $displayLivingCity;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_living_country", type="boolean")
     */
    private $displayLivingCountry;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_biography", type="boolean")
     */
    private $displayBiography;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_interest", type="boolean")
     */
    private $displayInterest;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_food_allergy", type="boolean")
     */
    private $displayFoodAllergy;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_marital_status", type="boolean")
     */
    private $displayMaritalStatus;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ApplicationUser Utilisateur ayant créé le groupe (Relation Bidirectionnelle)
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", inversedBy="contactGroups" )
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * Contacts in this group     *
     * @var ArrayCollection of Contact
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User\Contact", mappedBy="groups")
     */
    private $contacts;

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
     * @return ContactGroup
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayFirstName()
    {
        return $this->displayFirstName;
    }

    /**
     * @param boolean $displayFirstName
     * @return ContactGroup
     */
    public function setDisplayFirstName($displayFirstName)
    {
        $this->displayFirstName = $displayFirstName;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayLastName()
    {
        return $this->displayLastName;
    }

    /**
     * @param boolean $displayLastName
     * @return ContactGroup
     */
    public function setDisplayLastName($displayLastName)
    {
        $this->displayLastName = $displayLastName;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayGender()
    {
        return $this->displayGender;
    }

    /**
     * @param boolean $displayGender
     * @return ContactGroup
     */
    public function setDisplayGender($displayGender)
    {
        $this->displayGender = $displayGender;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayBirthday()
    {
        return $this->displayBirthday;
    }

    /**
     * @param boolean $displayBirthday
     * @return ContactGroup
     */
    public function setDisplayBirthday($displayBirthday)
    {
        $this->displayBirthday = $displayBirthday;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayNationality()
    {
        return $this->displayNationality;
    }

    /**
     * @param boolean $displayNationality
     * @return ContactGroup
     */
    public function setDisplayNationality($displayNationality)
    {
        $this->displayNationality = $displayNationality;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayLivingCity()
    {
        return $this->displayLivingCity;
    }

    /**
     * @param boolean $displayLivingCity
     * @return ContactGroup
     */
    public function setDisplayLivingCity($displayLivingCity)
    {
        $this->displayLivingCity = $displayLivingCity;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayLivingCountry()
    {
        return $this->displayLivingCountry;
    }

    /**
     * @param boolean $displayLivingCountry
     * @return ContactGroup
     */
    public function setDisplayLivingCountry($displayLivingCountry)
    {
        $this->displayLivingCountry = $displayLivingCountry;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayBiography()
    {
        return $this->displayBiography;
    }

    /**
     * @param boolean $displayBiography
     * @return ContactGroup
     */
    public function setDisplayBiography($displayBiography)
    {
        $this->displayBiography = $displayBiography;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayInterest()
    {
        return $this->displayInterest;
    }

    /**
     * @param boolean $displayInterest
     * @return ContactGroup
     */
    public function setDisplayInterest($displayInterest)
    {
        $this->displayInterest = $displayInterest;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayFoodAllergy()
    {
        return $this->displayFoodAllergy;
    }

    /**
     * @param boolean $displayFoodAllergy
     * @return ContactGroup
     */
    public function setDisplayFoodAllergy($displayFoodAllergy)
    {
        $this->displayFoodAllergy = $displayFoodAllergy;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisplayMaritalStatus()
    {
        return $this->displayMaritalStatus;
    }

    /**
     * @param boolean $displayMaritalStatus
     * @return ContactGroup
     */
    public function setDisplayMaritalStatus($displayMaritalStatus)
    {
        $this->displayMaritalStatus = $displayMaritalStatus;
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
     * @return ContactGroup
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     * @return ContactGroup
     */
    public function addContact(Contact $contact)
    {
        $this->contacts[] = $contact;
        $contact->addGroup($this);
        return $this;
    }
    /**
     * @param Contact $contacts
     * @return ContactGroup
     */
    public function removeContact(Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
        $contacts->removeGroup($this);
        return $this;
    }

    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

}

