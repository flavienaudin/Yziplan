<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 10:45
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Entity\User\Contact;
use AppBundle\Entity\User\ContactEmail;
use AppBundle\Entity\User\ContactGroup;
use AppBundle\Utils\enum\AppUserStatus;
use AppBundle\Utils\enum\ContactInfoType;
use AppBundle\Utils\enum\ContactStatus;
use AppBundle\Utils\enum\LegalStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUsers implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const USER_ONE_EMAIL = "user1@at.fr";
    const USER_TWO_EMAIL = "user2@at.fr";

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        // Get our userManager, you must implement `ContainerAwareInterface`
        $userManager = $this->container->get('at.manager.user');

        /*------------------
            AccountUser
        ------------------*/

        /** @var $userOne AccountUser */
        $userOne = $userManager->createUser();
        $userOne->setUsername(self::USER_ONE_EMAIL);
        $userOne->setEmail(self::USER_ONE_EMAIL);
        $userOne->setPlainPassword('user1');
        $userOne->setPasswordKnown(true);
        $userOne->setEnabled(true);
        $userOneAppEmail = new AppUserEmail();
        $userOneAppEmail->setEmail(self::USER_ONE_EMAIL);
        $userOneAppEmail->setUseToReceiveEmail(true);
        $userOne->getApplicationUser()->addAppUserEmail($userOneAppEmail);
        $userOne->getApplicationUser()->getAppUserInformation()->setPublicName("User One");
        $userOne->getApplicationUser()->getAppUserInformation()->setLegalStatus(LegalStatus::INDIVIDUAL);
        // Update the user for password
        $userManager->updateUser($userOne, true);

        /** @var $userTwo AccountUser */
        $userTwo = $userManager->createUser();
        $userTwo->setUsername(self::USER_TWO_EMAIL);
        $userTwo->setEmail(self::USER_TWO_EMAIL);
        $userTwo->setPlainPassword('user2');
        $userTwo->setPasswordKnown(true);
        $userTwo->setEnabled(true);
        $userTwoAppEmail = new AppUserEmail();
        $userTwoAppEmail->setEmail(self::USER_TWO_EMAIL);
        $userTwoAppEmail->setUseToReceiveEmail(true);
        $userTwo->getApplicationUser()->addAppUserEmail($userTwoAppEmail);
        $userTwo->getApplicationUser()->getAppUserInformation()->setPublicName("User two");
        $userTwo->getApplicationUser()->getAppUserInformation()->setLegalStatus(LegalStatus::INDIVIDUAL);
        // Update the user for password
        $userManager->updateUser($userTwo, true);


        /*------------------
            Contacts :
            User One
        ------------------*/
        $contactGroup1 = new ContactGroup();
        $contactGroup1->setOwner($userOne->getApplicationUser());
        $contactGroup1->setName("Amis");
        $contactGroup1->setDisplayFirstName(true);
        $contactGroup1->setDisplayLastName(true);
        $contactGroup1->setDisplayBirthday(true);
        $contactGroup1->setDisplayGender(true);
        $contactGroup1->setDisplayBiography(true);
        $contactGroup1->setDisplayInterest(true);
        $contactGroup1->setDisplayFoodAllergy(true);
        $contactGroup1->setDisplayLivingCountry(true);
        $contactGroup1->setDisplayLivingCity(true);
        $contactGroup1->setDisplayMaritalStatus(true);
        $contactGroup1->setDisplayNationality(true);
        $manager->persist($contactGroup1);

        $contact1 = new Contact();
        $contact1->setFirstName("Utilisateur");
        $contact1->setLastName("Deux");
        $contact1->setStatus(ContactStatus::VALID);
        $contact1AppEmail1 = new ContactEmail();
        $contact1AppEmail1->setEmail(self::USER_TWO_EMAIL);
        $contact1AppEmail1->setType(ContactInfoType::BUSINESS);
        $contact1->addContactEmail($contact1AppEmail1);
        $contact1->addLinked($userTwo->getApplicationUser());
        $contact1->addGroup($contactGroup1);
        $userOne->getApplicationUser()->addContact($contact1);
        $manager->persist($contact1);

        $contact2 = new Contact();
        $contact2->setFirstName("Julien");
        $contact2->setLastName("Portin");
        $contact2->setStatus(ContactStatus::VALID);
        $contact2AppEmail1 = new ContactEmail();
        $contact2AppEmail1->setEmail("julien.portin@at.fr");
        $contact2AppEmail1->setType(ContactInfoType::HOME);
        $contact2->addContactEmail($contact2AppEmail1);
        $userOne->getApplicationUser()->addContact($contact2);

        $manager->persist($contact2);
        $manager->flush();

    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}