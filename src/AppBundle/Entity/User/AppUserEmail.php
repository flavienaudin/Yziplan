<?php

namespace AppBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppUserEmail
 *
 * @ORM\Table(name="user_app_user_email")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\AppUserEmailRepository")
 */
class AppUserEmail
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
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="enum_contactinfo_type")
     */
    private $type;


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

