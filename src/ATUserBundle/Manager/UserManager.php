<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/05/2016
 * Time: 10:22
 */

namespace ATUserBundle\Manager;

use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Manager\GenerateursToken;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use FOS\UserBundle\Form\Factory\FormFactory;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends BaseManager
{
    /** @var GenerateursToken */
    protected $tokenGenerateur;
    /** @var  EntityManager */
    protected $entityManager;
    /** @var FormFactory */
    private $fosUserProfileFormFactory;

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
     * @param FormFactory $formFactory
     */
    public function setFosUserProfileFormFactory(FormFactory $formFactory)
    {
        $this->fosUserProfileFormFactory = $formFactory;
    }

    public function updateCanonicalFields(UserInterface $user)
    {
        parent::updateCanonicalFields($user);
        if ($user instanceof AccountUser) {
            /** @var AppUserEmail $appUserEMail */
            foreach ($user->getApplicationUser()->getAppUserEmails() as $appUserEMail) {
                $appUserEMail->setEmailCanonical($this->canonicalizeEmail($appUserEMail->getEmail()));
            }
        }
    }

    /**
     * @param AccountUser $user
     * @return FormInterface
     */
    public function createProfileForm(AccountUser $user)
    {
        $userForm = $this->fosUserProfileFormFactory->createForm();
        $userForm->setData($user);
        return $userForm;
    }

    /**
     * @param $email string Email to look for
     * @return AppUserEmail|null
     */
    public function findAppUserEmailByEmail($email)
    {
        return $this->entityManager->getRepository(AppUserEmail::class)->findOneBy(['emailCanonical' => $this->emailCanonicalizer->canonicalize($email)]);
    }
}