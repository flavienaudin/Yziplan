<?php

namespace ATUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAbout
 *
 * @ORM\Table(name="user_about")
 * @ORM\Entity(repositoryClass="ATUserBundle\Repository\UserAboutRepository")
 */
class UserAbout
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
     * @var User
     * @ORM\OneToOne(targetEntity="ATUserBundle\Entity\User", mappedBy="userAbout")
     */
    private $user;

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
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

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
     * @param \DateTime $birthday
     *
     * @return UserAbout
     */
    public function setBirthday($birthday)
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
}

