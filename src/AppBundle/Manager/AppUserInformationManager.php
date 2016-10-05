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
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Form\AppUserInfoContactDetailsType;
use ATUserBundle\Form\AppUserInformationBiographyType;
use ATUserBundle\Form\AppUserInfoPersonalType;
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
     * @param AccountUser $accountUSer
     * @return AppUserInformation
     */
    public function retrieveAppUserInformation(AccountUser $accountUSer)
    {
        $this->appUserInformation = null;
        if ($accountUSer->getApplicationUser() != null) {
            $this->appUserInformation = $accountUSer->getApplicationUser()->getAppUserInformation();
        }
        if ($this->appUserInformation == null) {
            $this->appUserInformation = new AppUserInformation();
            if ($accountUSer->getApplicationUser() == null) {
                $applicationUser = new ApplicationUser();
                $accountUSer->setApplicationUser($applicationUser);
                $applicationUser->setAccountUser($accountUSer);
            }
            $accountUSer->getApplicationUser()->setAppUserInformation($this->appUserInformation);
            $this->entityManager->persist($accountUSer);
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
    public function createPersonalInformationForm()
    {
        if ($this->appUserInformation != null) {
            return $this->formFactory->create(AppUserInfoPersonalType::class, $this->appUserInformation);
        }
        return null;
    }

    /**
     * @return FormInterface|null
     */
    public function createContactDetailsForm()
    {
        if ($this->appUserInformation != null) {
            return $this->formFactory->create(AppUserInfoContactDetailsType::class, $this->appUserInformation);
        }
        return null;
    }

    /**
     * @return FormInterface|null
     */
    public function createBiographyForm()
    {
        if ($this->appUserInformation != null) {
            return $this->formFactory->create(AppUserInformationBiographyType::class, $this->appUserInformation);
        }
        return null;
    }
}