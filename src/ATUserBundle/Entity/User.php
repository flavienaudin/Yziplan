<?php

namespace ATUserBundle\Entity;

use AppBundle\Entity\AppUser;
use FOS\UserBundle\Model\User as FosUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * User
 *
 * @ORM\Table(name="user_at")
 * @ORM\Entity(repositoryClass="ATUserBundle\Repository\UserRepository")
 */
class User extends FosUser
{
    /** Active les timestamps automatiques pour la creation et la mise a jour */
    use TimestampableEntity;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="change", field="enabled", value="true")
     * @ORM\Column(name="enabled_at", type="datetime", nullable=true)
     */
    private $enabledAt;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="googleId", type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @var string
     *
     * @ORM\Column(name="googleAccessToken", type="string", length=255, nullable=true)
     */
    private $googleAccessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookAccessToken", type="string", length=255, nullable=true)
     */
    private $facebookAccessToken;

    /**
     * @var UserAbout
     *
     * @ORM\OneToOne(targetEntity="ATUserBundle\Entity\UserAbout", inversedBy="user", cascade={"persist", "remove"}, orphanRemoval=true )
     * @ORM\JoinColumn(name="user_about_id", referencedColumnName="id")
     */
    private $userAbout;

    /**
     * @var string
     *
     * @ORM\Column(name="pseudo", type="string", length=255, nullable=true)
     */
    private $pseudo;

    /**
     * @var boolean true si l'utilisateur a choisi son mot de passe, false si le compte est créé via HwioAuth
     *
     * @ORM\Column(name="password_known", type="boolean")
     */
    private $passwordKnown = false;

    /**
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\AppUser", mappedBy="user")
     *
     * @var AppUser
     */
    private $appUser;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userAbout = new UserAbout();
        $this->userAbout->setUser($this);
    }

    public function __toString()
    {
        if(empty($this->pseudo)){
            return $this->username;
        }else{
            return $this->pseudo;
        }
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
     * @return \DateTime
     */
    public function getEnabledAt()
    {
        return $this->enabledAt;
    }

    /**
     * @param \DateTime $enabledAt
     * @return User
     */
    public function setEnabledAt($enabledAt)
    {
        $this->enabledAt = $enabledAt;
        return $this;
    }

    /**
     * @return bool true si au moins un des ID de ResSoc est renseigné
     */
    public function isSocialNetworkConnected(){
        return !(empty($this->googleId) && empty($this->facebookId));
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     *
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Set googleAccessToken
     *
     * @param string $googleAccessToken
     *
     * @return User
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->googleAccessToken = $googleAccessToken;

        return $this;
    }

    /**
     * Get googleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * @return UserAbout
     */
    public function getUserAbout()
    {
        return $this->userAbout;
    }

    /**
     * @param UserAbout $userAbout
     * @return User
     */
    public function setUserAbout(UserAbout $userAbout)
    {
        $this->userAbout = $userAbout;
        return $this;
    }

    /**
     * Set pseudo
     *
     * @param string $pseudo
     *
     * @return User
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @return boolean
     */
    public function isPasswordKnown()
    {
        return $this->passwordKnown;
    }

    /**
     * @param boolean $passwordKnown
     * @return User
     */
    public function setPasswordKnown($passwordKnown)
    {
        $this->passwordKnown = $passwordKnown;
        return $this;
    }

    /**
     * Get passwordKnown
     *
     * @return boolean
     */
    public function getPasswordKnown()
    {
        return $this->passwordKnown;
    }
    

    /**
     * Set appUser
     *
     * @param \AppBundle\Entity\AppUser $appUser
     *
     * @return User
     */
    public function setAppUser(\AppBundle\Entity\AppUser $appUser = null)
    {
        $this->appUser = $appUser;

        return $this;
    }

    /**
     * Get appUser
     *
     * @return \AppBundle\Entity\AppUser
     */
    public function getAppUser()
    {
        return $this->appUser;
    }
}
