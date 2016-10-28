<?php

namespace AppBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * AppUserInformation
 *
 * @ORM\Table(name="user_app_user_information")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\AppUserInformationRepository")
 */
class AppUserInformation
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
     * @ORM\Column(name="legal_status", type="enum_legal_status", nullable=true)
     */
    private $legalStatus = null;

    /**
     * @var string
     *
     * @ORM\Column(name="publicName", type="string", length=255, nullable=true)
     */
    private $publicName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="enum_gender", nullable=true)
     */
    private $gender = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=255, nullable=true)
     */
    private $nationality;

    /**
     * @var string
     *
     * @ORM\Column(name="living_city", type="string", length=255, nullable=true)
     */
    private $livingCity;

    /**
     * @var string
     *
     * @ORM\Column(name="living_country", type="string", length=255, nullable=true)
     */
    private $livingCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="biography", type="text", nullable=true)
     */
    private $biography;

    /**
     * @var string
     *
     * @ORM\Column(name="interests", type="text", nullable=true)
     */
    private $interests;

    /**
     * @var string
     *
     * @ORM\Column(name="food_conveniences", type="text", nullable=true)
     */
    private $foodConveniences;

    /**
     * @var string
     *
     * @ORM\Column(name="marital_status", type="enum_marital_status", nullable=true)
     */
    private $maritalStatus = null;

    /**
     * This attributes stores the filename of the file for the database, but also the File due to PostLoad event lstener to load the file (Cf. AvatarUploadListener class)
     * @var string|File
     * @ORM\Column(name="avatar_file_name", type="string", length=255, nullable=true)
     */
    private $avatar;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ApplicationUser
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", mappedBy="appUserInformation")
     *
     */
    private $applicationUser;


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
    public function getLegalStatus()
    {
        return $this->legalStatus;
    }

    /**
     * @param string $legalStatus
     * @return AppUserInformation
     */
    public function setLegalStatus($legalStatus)
    {
        $this->legalStatus = $legalStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicName()
    {
        return $this->publicName;
    }

    /**
     * @param string $publicName
     * @return AppUserInformation
     */
    public function setPublicName($publicName)
    {
        $this->publicName = $publicName;
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
     * @return AppUserInformation
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
     * @return AppUserInformation
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
     * @return AppUserInformation
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
     * @return AppUserInformation
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
     * @return AppUserInformation
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @return string
     */
    public function getLivingCity()
    {
        return $this->livingCity;
    }

    /**
     * @param string $livingCity
     * @return AppUserInformation
     */
    public function setLivingCity($livingCity)
    {
        $this->livingCity = $livingCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getLivingCountry()
    {
        return $this->livingCountry;
    }

    /**
     * @param string $livingCountry
     * @return AppUserInformation
     */
    public function setLivingCountry($livingCountry)
    {
        $this->livingCountry = $livingCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @param string $biography
     * @return AppUserInformation
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return string
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * @param string $interests
     * @return AppUserInformation
     */
    public function setInterests($interests)
    {
        $this->interests = $interests;
        return $this;
    }

    /**
     * @return string
     */
    public function getFoodConveniences()
    {
        return $this->foodConveniences;
    }

    /**
     * @param string $foodConveniences
     * @return AppUserInformation
     */
    public function setFoodConveniences($foodConveniences)
    {
        $this->foodConveniences = $foodConveniences;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * @param string $maritalStatus
     * @return AppUserInformation
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     * @return AppUserInformation
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return ApplicationUser
     */
    public function getApplicationUser()
    {
        return $this->applicationUser;
    }

    /**
     * @param ApplicationUser $applicationUser
     * @return AppUserInformation
     */
    public function setApplicationUser($applicationUser)
    {
        $this->applicationUser = $applicationUser;
        return $this;
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    public function getDisplayableName()
    {
        $displayableName = $this->publicName;
        if (empty($displayableName)) {
            // TODO check preferences before using other data
            // $displayableName = $this->firstName . " " . $this->lastName;
        }
        return $displayableName;
    }

}
