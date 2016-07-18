<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/05/2016
 * Time: 10:22
 */

namespace ATUserBundle\Manager;

use AppBundle\Manager\GenerateursToken;
use ATUserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends BaseManager
{
    /** @var GenerateursToken */
    protected $tokenGenerateur;
    /** @var  EntityManager */
    protected $entityManager;

    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);
    }

    /**
     * @param GenerateursToken $tokenGenerateur
     */
    public function setTokenGenerateur(GenerateursToken $tokenGenerateur)
    {
        $this->tokenGenerateur = $tokenGenerateur;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create a user from the given email. The user is disabled and a random password is set.
     * 
     * @param $email
     * @return User
     */
    public function createUserFromEmail($email){
        /** @var User $user */
        $user = $this->createUser();
        $user->setEmail($email);
        $user->setUsername($email);

        if($user instanceof User) {
            $user->setPseudo(explode("@", $email)[0]);
        }
        $user->setPlainPassword($this->tokenGenerateur->random(GenerateursToken::MOTDEPASSE_LONGUEUR));
        $user->setEnabled(false);
        $user->setPasswordKnown(false);
        $this->updateUser($user);
        return $user;
    }
}