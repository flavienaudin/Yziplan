<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 12:26
 */

namespace ATUserBundle\Manager;

use ATUserBundle\Entity\User;
use ATUserBundle\Entity\UserAbout;
use Doctrine\ORM\EntityManager;

class UserAboutManager
{
    /** @var  EntityManager */
    protected $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param UserAbout $userAbout Infoirmation utilisateur à mettre à jour en base de données
     * @return bool
     */
    public function updateUserAbout(UserAbout $userAbout)
    {
        $this->entityManager->persist($userAbout);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Get the UserAbout and create it if null
     * @param User $user
     * @return UserAbout
     */
    public function getUserAbout(User $user)
    {
        $userAbout = $user->getUserAbout();
        if ($userAbout == null) {
            $userAbout = new UserAbout();
            $user->setUserAbout($userAbout);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $userAbout;
    }
}