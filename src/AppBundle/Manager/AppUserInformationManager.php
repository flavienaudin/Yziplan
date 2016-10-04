<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 12:26
 */

namespace AppBundle\Manager;


use AppBundle\Entity\User\AppUserInformation;
use ATUserBundle\Entity\AccountUser;
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
        $this->appUserInformation = $accountUSer->getApplicationUser()->getAppUserInformation();
        if ($this->appUserInformation == null) {
            $this->appUserInformation = new AppUserInformation();
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