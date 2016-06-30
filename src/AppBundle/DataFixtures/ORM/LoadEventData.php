<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 13/06/2016
 * Time: 10:52
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\AppUser;
use AppBundle\Entity\enum\EventStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\ExpenseModule;
use AppBundle\Entity\module\ExpenseProposal;
use AppBundle\Entity\module\PollModule;
use AppBundle\Entity\module\Proposal;
use AppBundle\Manager\GenerateursToken;
use ATUserBundle\Entity\User;
use ATUserBundle\Manager\UtilisateurManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEventData implements FixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get our userManager, you must implement `ContainerAwareInterface`
        $userManager = $this->container->get('at.manager.user_manager');
        $tokenManager = $this->container->get('at.service.gentoken');

        /** @var $userprincipal User */
        $userprincipal = $userManager->createUser();
        $userprincipal->setUsername("user1@at.fr");
        $userprincipal->setEmail('user1@at.fr');
        $userprincipal->setPlainPassword('user1');
        $userprincipal->setPasswordKnown(true);
        $userprincipal->setEnabled(true);
        // Update the user for password
        $userManager->updateUser($userprincipal, true);


        /** @var $userInvite User */
        $userInvite = $userManager->createUser();
        $userInvite->setUsername('user2@at.fr');
        $userInvite->setEmail('user2@at.fr');
        $userInvite->setPlainPassword('user2');
        $userInvite->setPasswordKnown(true);
        $userInvite->setEnabled(true);
        // Update the user for password
        $userManager->updateUser($userInvite, true);

        
        //Event
        $event = new Event();
        $event->setName("Test d'evenement");
        $event->setToken("123456789");
        $event->setTokenEdition("987654321");
        $event->setStatus(EventStatus::IN_ORGANIZATION);
        $event->setDescription("Nouvel événément super cool de test. C'est donc sa description");

        //EventInvitation Creator
        $eventInvitationCreator = new EventInvitation();
        $eventInvitationCreator->setName("Jacky");
        $userprincipal->getAppUser()->addEventInvitation($eventInvitationCreator);
        $event->addEventInvitation($eventInvitationCreator);
        $event->setCreator($eventInvitationCreator);

        //EventInvitation Invite
        $eventInvitation = new EventInvitation();
        $eventInvitation->setName("Raymond");
        $userInvite->getAppUser()->addEventInvitation($eventInvitation);
        $event->addEventInvitation($eventInvitation);

        //Module
        $module = new Module();
        $event->addModule($module);
        $module->setName("Test Expense Module");
        $module->setDescription("Ceci est une super description.

        Avec un saut à la ligne!");
        $module->setToken("12345");
        $module->setTokenEdition("543221");
        $module->setStatus(EventStatus::IN_ORGANIZATION);

       //Module specifique pour les depenses
        $expenseModule = new ExpenseModule();
        $module->setExpenseModule($expenseModule);
        
        $expenseProposal = new ExpenseProposal();
        $expenseProposal->setName("Restaurant");
        $expenseProposal->setAmount(12);
        $expenseProposal->setExpenseDate(new \DateTime());
        $expenseProposal->setCreator($eventInvitationCreator);
        $expenseProposal->setPayer($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitation);
        $expenseModule->addExpenseProposal($expenseProposal);
        
        $expenseProposal2 = new ExpenseProposal();
        $expenseProposal2->setName("Restaurant2");
        $expenseProposal2->setAmount(36);
        $expenseProposal2->setExpenseDate(new \DateTime());
        $expenseProposal2->setCreator($eventInvitation);
        $expenseProposal2->setPayer($eventInvitation);
        $expenseProposal2->addListOfParticipant($eventInvitationCreator);
        $expenseProposal2->addListOfParticipant($eventInvitation);
        $expenseModule->addExpenseProposal($expenseProposal2);

        // Module Poll
        $modulePoll = new Module();
        $event->addModule($modulePoll);
        $modulePoll->setName("Module Quand");
        $modulePoll->setDescription("C'est un chouette module");
        $modulePoll->setToken("quand123");
        $modulePoll->setTokenEdition("quand321");
        $modulePoll->setStatus(EventStatus::IN_ORGANIZATION);

        $pollModule = new PollModule();
        $modulePoll->setPollModule($pollModule);

        $pollProposal = new Proposal();
        $pollProposal->setName("PollModuleProposal Nom");
        $pollProposal->setDescription("PollModule Proposal description");
        $pollModule->addProposal($pollProposal);

        $manager->persist($event);
        $manager->flush();
    }
}