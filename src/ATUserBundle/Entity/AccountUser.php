<?php

namespace ATUserBundle\Entity;

use AppBundle\Entity\User\ApplicationUser;
use FOS\UserBundle\Model\User as FosUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * AccountUser
 *
 * @ORM\Table(name="atuser_account_user")
 * @ORM\Entity(repositoryClass="ATUserBundle\Repository\AccountUserRepository")
 */
class AccountUser extends FosUser
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
     * @var boolean true si l'utilisateur a choisi son mot de passe, false si le compte est créé via HwioAuth
     *
     * @ORM\Column(name="password_known", type="boolean")
     */
    private $passwordKnown = false;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ApplicationUser
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", mappedBy="accountUser")
     */
    private $applicationUser;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        parent::__construct();
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
     * @return \DateTime
     */
    public function getEnabledAt()
    {
        return $this->enabledAt;
    }

    /**
     * @param \DateTime $enabledAt
     * @return AccountUser
     */
    public function setEnabledAt($enabledAt)
    {
        $this->enabledAt = $enabledAt;
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
     * Set googleId
     *
     * @param string $googleId
     *
     * @return AccountUser
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

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
     * Set googleAccessToken
     *
     * @param string $googleAccessToken
     *
     * @return AccountUser
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->googleAccessToken = $googleAccessToken;

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
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return AccountUser
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

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
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return AccountUser
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
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
     * @return AccountUser
     */
    public function setPasswordKnown($passwordKnown)
    {
        $this->passwordKnown = $passwordKnown;
        return $this;
    }

    /**
     * Get applicationUser
     *
     * @return ApplicationUser
     */
    public function getApplicationUser()
    {
        return $this->applicationUser;
    }

    /**
     * Set applicationUser
     *
     * @param ApplicationUser $applicationUser
     *
     * @return AccountUser
     */
    public function setApplicationUser($applicationUser)
    {
        $this->applicationUser = $applicationUser;

        return $this;
    }

    /***********************************************************************
     * Helpers
     ***********************************************************************/

    /**
     * @return bool true si au moins un des ID de ResSoc est renseigné
     */
    public function isSocialNetworkConnected()
    {
        return !(empty($this->googleId) && empty($this->facebookId));
    }
}
