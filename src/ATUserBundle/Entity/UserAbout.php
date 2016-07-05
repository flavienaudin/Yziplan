<?php

namespace ATUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * UserAbout
 *
 * @ORM\Table(name="user_about")
 * @ORM\Entity(repositoryClass="ATUserBundle\Repository\UserAboutRepository")
 */
class UserAbout
{
    const LEGAL = "LEGAL";
    const NATURAL = "NATURAL";
    
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
     * @var User
     * @ORM\OneToOne(targetEntity="ATUserBundle\Entity\User", mappedBy="userAbout")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="fullname", type="string", length=127, nullable=true)
     */
    private $fullname;

    /**
     * @var string
     *
     * @ORM\Column(name="biography", type="string", length=511, nullable=true)
     */
    private $biography;

    /**
     * @var int
     *
     * @ORM\Column(name="gender", type="smallint", nullable=true)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_name", type="string", length=255, nullable=true)
     */
    private $facebookName;

    /**
     * @var string
     *
     * @ORM\Column(name="google_name", type="string", length=255, nullable=true)
     */
    private $googleName;

    /*************************************************************************************************
     *            Informations personnelles needed for MangoPay
     *************************************************************************************************/

    /**
     * Value:
     * - NATURAL
     * - LEGAL
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $userType;

    /**
     * First name of the user or of the legal representative
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * Last name of the user or of the legal representative
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * Birthday of the user or of the legal representative
     *
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * The user or legal representative's nationality. ISO 3166-1 alpha-2 format is expected
     *
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=2, nullable=true)
     */
    private $nationality;

    /**
     * TThe user or legal representative's country of residence. ISO 3166-1 alpha-2 format is expected
     *
     * @var string
     *
     * @ORM\Column(name="country_of_residence", type="string", length=2, nullable=true)
     */
    private $countryOfResidence;

    //************************* For legal user ***********************************

    /**
     * @var string
     *
     * @ORM\Column(name="business_name", type="string", length=255, nullable=true)
     */
    private $businessName;

    /**
     * @var string
     *
     * @ORM\Column(name="generic_business_email", type="string", length=255, nullable=true)
     */
    private $genericBusinessEmail;


    /*************************************************************************************************
     *            Getter and Setter
     *************************************************************************************************/


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
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return UserAbout
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param string $fullname
     * @return UserAbout
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * Set biography
     *
     * @param string $biography
     *
     * @return UserAbout
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * Get biography
     *
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     *
     * @return UserAbout
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set birthday
     *
     * @param \DateTime|null $birthday
     *
     * @return UserAbout
     */
    public function setBirthday(\DateTime $birthday=null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set facebookName
     *
     * @param string $facebookName
     *
     * @return UserAbout
     */
    public function setFacebookName($facebookName)
    {
        $this->facebookName = $facebookName;

        return $this;
    }

    /**
     * Get facebookName
     *
     * @return string
     */
    public function getFacebookName()
    {
        return $this->facebookName;
    }

    /**
     * Set googleName
     *
     * @param string $googleName
     *
     * @return UserAbout
     */
    public function setGoogleName($googleName)
    {
        $this->googleName = $googleName;

        return $this;
    }

    /**
     * Get googleName
     *
     * @return string
     */
    public function getGoogleName()
    {
        return $this->googleName;
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param string $userType
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
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
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
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
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
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
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @return string
     */
    public function getCountryOfResidence()
    {
        return $this->countryOfResidence;
    }

    /**
     * @param string $countryOfResidence
     */
    public function setCountryOfResidence($countryOfResidence)
    {
        $this->countryOfResidence = $countryOfResidence;
    }

    /**
     * @return string
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * @param string $businessName
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;
    }

    /**
     * @return string
     */
    public function getGenericBusinessEmail()
    {
        return $this->genericBusinessEmail;
    }

    /**
     * @param string $genericBusinessEmail
     */
    public function setGenericBusinessEmail($genericBusinessEmail)
    {
        $this->genericBusinessEmail = $genericBusinessEmail;
    }
    
    
}

