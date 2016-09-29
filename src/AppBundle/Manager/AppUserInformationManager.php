<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 12:26
 */

namespace AppBundle\Manager;


use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserInformation;
use ATUserBundle\Form\UserAboutBasicInformationType;
use ATUserBundle\Form\UserAboutBiographyType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class AppUserInformationManager
{
    /** @var  EntityManager $entityManager */
    private $entityManager;
    /** @var FormFactoryInterface $formFactory */
    private $formFactory;
    /** @var AppUserInformation $AppUserInformation */
    private $appUserInformation;

    public function __construct(EntityManager $em, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $em;
        $this->formFactory = $formFactory;
    }

    /**
     * Get the AppUserInformation and create it if null
     * @param ApplicationUser $applicationUser
     * @return AppUserInformation
     */
    public function retrieveAppUserInformation(ApplicationUser $applicationUser)
    {
        $this->appUserInformation = $applicationUser->getAppUserInformation();
        if ($this->appUserInformation == null) {
            $this->appUserInformation = new AppUserInformation();
            $applicationUser->setAppUserInformation($this->appUserInformation);
            $this->entityManager->persist($applicationUser);
            $this->entityManager->flush();
        }
        return $this->appUserInformation;
    }

    /**
     * @param AppUserInformation $appUserInformation Information utilisateur à mettre à jour en base de données
     * @return bool
     */
    public function updateAppUserInformation(AppUserInformation $appUserInformation)
    {
        $this->entityManager->persist($appUserInformation);
        $this->entityManager->flush();
        return true;
    }

    /**
     * @return FormInterface|null
     */
    public function createBiographyForm()
    {
        if ($this->appUserInformation != null) {
            // TODO return $this->formFactory->create(UserAboutBiographyType::class, $this->appUserInformation);
        }
        return null;
    }

    /**
     * @return FormInterface|null
     */
    public function createBasicInformationForm()
    {
        if ($this->appUserInformation != null) {
            // TODO return $this->formFactory->create(UserAboutBasicInformationType::class, $this->appUserInformation);
        }
        return null;
    }
}