<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/09/2016
 * Time: 15:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\AppUser;
use ATUserBundle\Entity\User;

class AppUserManager
{

    /** @var AppUser */
    private $appUser;

    /**
     * @return AppUser
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

    /**
     * @param AppUser $appUser
     */
    public function setAppUser($appUser)
    {
        $this->appUser = $appUser;
    }

    public function getUserEventInvitations(User $user = null){
        if($user instanceof User){
            $this->appUser = $user->getAppUser();
        }
        if($this->appUser != null){
            return $this->appUser->getEventInvitations();
        }
        return null;
    }
}