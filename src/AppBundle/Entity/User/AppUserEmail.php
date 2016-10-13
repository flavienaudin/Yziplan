<?php

namespace AppBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * AppUserEmail
 *
 * @ORM\Table(name="user_app_user_email")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\AppUserEmailRepository")
 */
class AppUserEmail
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
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true)
     */
    private $emailCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="enum_contactinfo_type", nullable=true)
     */
    private $type;

    /**
     * @var boolean
     * @ORM\COlumn(name="use_to_receive_email", type="boolean")
     */
    private $useToReceiveEmail;

    /**
     * Random string sent to the user email address in order to verify it     *
     * @var string
     * @ORM\Column(name="confirmation_token", type="string", length=180, unique=true, nullable=true)
     */
    protected $confirmationToken;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ApplicationUser
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User\ApplicationUser", inversedBy="appUserEmails")
     * @ORM\JoinColumn(name="application_user_id", referencedColumnName="id")
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return AppUserEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * @param string $emailCanonical
     * @return AppUserEmail
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AppUserEmail
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUseToReceiveEmail()
    {
        return $this->useToReceiveEmail;
    }

    /**
     * @param boolean $useToReceiveEmail
     * @return AppUserEmail
     */
    public function setUseToReceiveEmail($useToReceiveEmail)
    {
        $this->useToReceiveEmail = $useToReceiveEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param string $confirmationToken
     * @return AppUserEmail
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
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
     * @return AppUserEmail
     */
    public function setApplicationUser($applicationUser)
    {
        $this->applicationUser = $applicationUser;
        return $this;
    }

}

