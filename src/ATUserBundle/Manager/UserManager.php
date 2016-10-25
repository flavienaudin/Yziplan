<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/05/2016
 * Time: 10:22
 */

namespace ATUserBundle\Manager;

use AppBundle\Entity\User\AppUserEmail;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use FOS\UserBundle\Form\Factory\FormFactory;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\Form\FormInterface;

class UserManager extends BaseManager
{
    /** @var CanonicalFieldsUpdater Surcharge de l'attribut de la classe parente */
    protected $canonicalFieldsUpdater;
    /** @var FormFactory */
    private $fosUserProfileFormFactory;

    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
    }

    /**
     * @param FormFactory $formFactory
     */
    public function setFosUserProfileFormFactory(FormFactory $formFactory)
    {
        $this->fosUserProfileFormFactory = $formFactory;
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
        return $this->objectManager->getRepository(AppUserEmail::class)->findOneBy(['emailCanonical' => $this->canonicalFieldsUpdater->canonicalizeEmail($email)]);
    }
}