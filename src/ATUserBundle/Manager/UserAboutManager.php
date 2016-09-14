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
use ATUserBundle\Form\UserAboutBasicInformationType;
use ATUserBundle\Form\UserAboutBiographyType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UserAboutManager
{
    /** @var  EntityManager $entityManager */
    private $entityManager;
    /** @var FormFactoryInterface $formFactory */
    private $formFactory;
    /** @var UserAbout $userAbout */
    private $userAbout;

    public function __construct(EntityManager $em, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $em;
        $this->formFactory = $formFactory;
    }

    /**
     * @return UserAbout
     */
    public function getUserAbout()
    {
        return $this->userAbout;
    }

    /**
     * @param UserAbout|null $userAbout
     */
    public function setUserAbout($userAbout = null)
    {
        $this->userAbout = $userAbout;
    }

    /**
     * Get the UserAbout and create it if null
     * @param User $user
     * @return UserAbout
     */
    public function retrieveUserAbout(User $user)
    {
        $this->userAbout = $user->getUserAbout();
        if ($this->userAbout == null) {
            $this->userAbout = new UserAbout();
            $user->setUserAbout($this->userAbout);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        return $this->userAbout;
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
     * @return FormInterface|null
     */
    public function createBiographyForm()
    {
        if ($this->userAbout != null) {
            return $this->formFactory->create(UserAboutBiographyType::class, $this->userAbout);
        }
        return null;
    }

    /**
     * @return FormInterface|null
     */
    public function createBasicInformationForm()
    {
        if ($this->userAbout != null) {
            return $this->formFactory->create(UserAboutBasicInformationType::class, $this->userAbout);
        }
        return null;
    }
}