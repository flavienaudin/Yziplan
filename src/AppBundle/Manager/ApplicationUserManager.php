<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/09/2016
 * Time: 15:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserEmail;
use ATUserBundle\Entity\AccountUser;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\CanonicalizerInterface;

class ApplicationUserManager
{

    /** @var EntityManager $entityManager */
    private $entityManager;
    /** @var CanonicalizerInterface $emailCanonicalizer */
    private $emailCanonicalizer;

    /** @var ApplicationUser */
    private $applicationUser;

    /**
     * ApplicationUserManager constructor.
     * @param EntityManager $entityManager
     * @param CanonicalizerInterface $emailCanonicalizer
     */
    public function __construct(EntityManager $entityManager, CanonicalizerInterface $emailCanonicalizer)
    {
        $this->entityManager = $entityManager;
        $this->emailCanonicalizer = $emailCanonicalizer;
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
     */
    public function setApplicationUser(ApplicationUser $applicationUser)
    {
        $this->applicationUser = $applicationUser;
    }

    /**
     * Retrieve the ApplicationUser by it AppUserEmail
     * @param $email
     * @return ApplicationUser|null
     */
    public function retrieveApplicationUserByEmail($email)
    {
        $this->applicationUser = null;
        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $this->findAppUserEmailByEmail($email);
        if ($appUserEmail != null) {
            $this->applicationUser = $appUserEmail->getApplicationUser();
        }
        return $this->applicationUser;
    }

    /**
     * @param AccountUser|null $user
     * @return \Doctrine\Common\Collections\Collection|null
     */
    public function getUserEventInvitations(AccountUser $user = null)
    {
        if ($user instanceof AccountUser) {
            $this->applicationUser = $user->getApplicationUser();
        }
        if ($this->applicationUser != null) {
            return $this->applicationUser->getEventInvitations();
        }
        return null;
    }

    /**
     * @param $email string Email to look for
     * @return ApplicationUser|null
     */
    public function findAppUserEmailByEmail($email)
    {
        return $this->entityManager->getRepository(AppUserEmail::class)->findOneBy(['emailCanonical' => $this->emailCanonicalizer->canonicalize($email)]);

    }


}