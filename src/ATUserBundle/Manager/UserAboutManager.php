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
use Symfony\Component\HttpFoundation\Request;

class UserAboutManager
{
    /** @var  EntityManager */
    protected $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager=$em;
    }

    /**
     * @param User $user Utilisateur à mettre à jour
     * @param string $biography La biography de l'utilisateur
     * @return bool
     */
    public function updateBiography(User $user, $biography){
        $userAbout = $this->getUserAbout($user);
        $userAbout->setBiography($biography);
        $this->entityManager->persist($userAbout);
        $this->entityManager->flush();
        return true;
    }


    private function getUserAbout(User $user){
        $userAbout = $user->getUserAbout();
        if($userAbout == null){
            $userAbout = new UserAbout();
            $user->setUserAbout($userAbout);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $userAbout;
    }
}