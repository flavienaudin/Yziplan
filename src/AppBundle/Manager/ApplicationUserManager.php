<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/09/2016
 * Time: 15:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\User\ApplicationUser;
use ATUserBundle\Entity\AccountUser;

class ApplicationUserManager
{

    /** @var ApplicationUser */
    private $applicationUser;

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

    public function getUserEventInvitations(AccountUser $user = null){
        if($user instanceof AccountUser){
            $this->applicationUser = $user->getApplicationUser();
        }
        if($this->applicationUser != null){
            return $this->applicationUser->getEventInvitations();
        }
        return null;
    }
}