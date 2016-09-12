<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 13/06/2016
 * Time: 10:52
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\enum\EventInvitationStatus;
use AppBundle\Entity\enum\EventStatus;
use AppBundle\Entity\enum\ModuleInvitationStatus;
use AppBundle\Entity\enum\ModuleStatus;
use AppBundle\Entity\enum\PollModuleSortingType;
use AppBundle\Entity\enum\PollProposalElementType;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\ExpenseModule;
use AppBundle\Entity\module\ExpenseProposal;
use AppBundle\Entity\module\PollModule;
use AppBundle\Entity\module\PollProposal;
use AppBundle\Entity\module\PollProposalElement;
use AppBundle\Entity\ModuleInvitation;
use AppBundle\Manager\GenerateursToken;
use ATUserBundle\Entity\User;
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
        $eventManager = $this->container->get('at.manager.event');

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
        $event->setGuestsCanAddModule(true);

        //EventInvitation Creator
        $eventInvitationCreator = new EventInvitation();
        $eventInvitationCreator->setName("Jacky");
        $eventInvitationCreator->setStatus(EventInvitationStatus::VALID);
        $eventInvitationCreator->setToken('azerty');
        $eventInvitationCreator->setTokenEdition('ytreza');
        $userprincipal->getAppUser()->addEventInvitation($eventInvitationCreator);
        $event->addEventInvitation($eventInvitationCreator);
        $event->setCreator($eventInvitationCreator);

        //EventInvitation Invite
        $eventInvitationRaymond = new EventInvitation();
        $eventInvitationRaymond->setName("Raymond");
        $eventInvitationRaymond->setStatus(EventInvitationStatus::VALID);
        $eventInvitationRaymond->setToken('qsdfgh');
        $eventInvitationRaymond->setTokenEdition('hgfdsq');
        $userInvite->getAppUser()->addEventInvitation($eventInvitationRaymond);
        $event->addEventInvitation($eventInvitationRaymond);

        //Module
        $moduleExpense = new Module();
        $event->addModule($moduleExpense);
        $moduleExpense->setName("Test Expense Module");
        $moduleExpense->setDescription("Ceci est une super description.

        Avec un saut à la ligne!");
        $moduleExpense->setToken("12345");
        $moduleExpense->setTokenEdition("543221");
        $moduleExpense->setStatus(EventStatus::IN_ORGANIZATION);
        $moduleExpense->setOrderIndex(2);

       //Module specifique pour les depenses
        $expenseModule = new ExpenseModule();
        $moduleExpense->setExpenseModule($expenseModule);

        $expenseProposal = new ExpenseProposal();
        $expenseProposal->setName("Restaurant");
        $expenseProposal->setAmount(12);
        $expenseProposal->setExpenseDate(new \DateTime());
        $expenseProposal->setCreator($eventInvitationCreator);
        $expenseProposal->setPayer($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitationRaymond);
        $expenseModule->addExpenseProposal($expenseProposal);
        
        $expenseProposal2 = new ExpenseProposal();
        $expenseProposal2->setName("Restaurant2");
        $expenseProposal2->setAmount(36);
        $expenseProposal2->setExpenseDate(new \DateTime());
        $expenseProposal2->setCreator($eventInvitationRaymond);
        $expenseProposal2->setPayer($eventInvitationRaymond);
        $expenseProposal2->addListOfParticipant($eventInvitationCreator);
        $expenseProposal2->addListOfParticipant($eventInvitationRaymond);
        $expenseModule->addExpenseProposal($expenseProposal2);

        $expenseModuleInvitation1 = new ModuleInvitation();
        $expenseModuleInvitation1->setModule($moduleExpense);
        $expenseModuleInvitation1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $expenseModuleInvitation1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $expenseModuleInvitation1->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $eventInvitationCreator->addModuleInvitation($expenseModuleInvitation1);

        $expenseModuleInvitation2 = new ModuleInvitation();
        $expenseModuleInvitation2->setModule($moduleExpense);
        $expenseModuleInvitation2->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $expenseModuleInvitation2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $expenseModuleInvitation2->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleExpense->setCreator($expenseModuleInvitation2);
        $eventInvitationRaymond->addModuleInvitation($expenseModuleInvitation2);


        // Module Poll
        $modulePoll = new Module();
        $event->addModule($modulePoll);
        $modulePoll->setName("Module Quand");
        $modulePoll->setDescription("C'est un chouette module");
        $modulePoll->setToken("quand123");
        $modulePoll->setTokenEdition("quand321");
        $modulePoll->setStatus(EventStatus::IN_ORGANIZATION);
        $modulePoll->setOrderIndex(1);

        $pollModule = new PollModule();
        $pollModule->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
        $modulePoll->setPollModule($pollModule);

        $pollProposal1 = new PollProposal();
        $pollProposalElement1 = new PollProposalElement();
        $pollProposalElement1->setName("Occasion");
        $pollProposalElement1->setType(PollProposalElementType::STRING);
        $pollProposalElement1->setValString("Crémaillère");
        $pollProposal1->addPollProposalElement($pollProposalElement1);
        $pollProposalElement2 = new PollProposalElement();
        $pollProposalElement2->setName("Complt.");
        $pollProposalElement2->setType(PollProposalElementType::STRING);
        $pollProposalElement2->setValString("Party");
        $pollProposal1->addPollProposalElement($pollProposalElement2);
        $pollModule->addPollProposal($pollProposal1);

        $pollProposal2 = new PollProposal();
        $pollProposalElement11 = new PollProposalElement();
        $pollProposalElement11->setName("Occasion");
        $pollProposalElement11->setType(PollProposalElementType::STRING);
        $pollProposalElement11->setValString("Anniversaire");
        $pollProposal2->addPollProposalElement($pollProposalElement11);
        $pollProposalElement22 = new PollProposalElement();
        $pollProposalElement22->setName("Complt.");
        $pollProposalElement22->setType(PollProposalElementType::STRING);
        $pollProposalElement22->setValString("30 ans");
        $pollProposal2->addPollProposalElement($pollProposalElement22);
        $pollModule->addPollProposal($pollProposal2);

        $moduleInvitation1 = new ModuleInvitation();
        $moduleInvitation1->setModule($modulePoll);
        $moduleInvitation1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleInvitation1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation1->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $modulePoll->setCreator($moduleInvitation1);
        $moduleInvitation1->initPollModuleResponse();
        $eventInvitationCreator->addModuleInvitation($moduleInvitation1);

        $pollProposal1->setCreator($moduleInvitation1);
        $pollProposal2->setCreator($moduleInvitation1);

        $moduleInvitation2 = new ModuleInvitation();
        $moduleInvitation2->setModule($modulePoll);
        $moduleInvitation2->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleInvitation2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation2->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation2->initPollModuleResponse();
        $eventInvitationRaymond->addModuleInvitation($moduleInvitation2);

        $manager->persist($event);
        $manager->flush();


        //Event 2
        $event2 = new Event();
        $event2->setName("Evénement 2");
        $event2->setToken("012345678");
        $event2->setTokenEdition("876543210");
        $event2->setStatus(EventStatus::IN_ORGANIZATION);
        $event2->setDescription("Evénément 2 organisé par un utilisateur anonyme");
        $event2->setGuestsCanInvite(false);
        $event2->setInvitationOnly(false);
        $event2->setGuestsCanAddModule(true);

        //EventInvitation Creator
        $event2InvitationCreator = new EventInvitation();
        $event2InvitationCreator->setName("Anon. 1");
        $event2InvitationCreator->setStatus(EventInvitationStatus::VALID);
        $event2InvitationCreator->setToken('0azerty');
        $event2InvitationCreator->setTokenEdition('ytreza0');
        $event2->addEventInvitation($event2InvitationCreator);
        $event2->setCreator($event2InvitationCreator);

        // Module Event 2
        $moduleEvent2 = new Module();
        $moduleEvent2->setStatus(ModuleStatus::IN_CREATION);
        $moduleEvent2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $pollModuleEvent2 = new PollModule();
        $pollModuleEvent2->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
        $moduleEvent2->setPollModule($pollModuleEvent2);
        $event2->addModule($moduleEvent2);

        // Module Event 2 Invitation Creator
        $moduleEvent2InvitationCreator = new ModuleInvitation();
        $moduleEvent2InvitationCreator->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationCreator->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationCreator->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationCreator);
        $moduleEvent2->setCreator($moduleEvent2InvitationCreator);
        $event2InvitationCreator->addModuleInvitation($moduleEvent2InvitationCreator);

        //EventInvitation Guest 1
        $event2InvitationGuest1 = new EventInvitation();
        $event2InvitationGuest1->setName("Guest anon. 1");
        $event2InvitationGuest1->setStatus(EventInvitationStatus::VALID);
        $event2InvitationGuest1->setToken('guest1');
        $event2InvitationGuest1->setTokenEdition('1guest');
        $event2->addEventInvitation($event2InvitationGuest1);

        // Module Event 2 Invitation Creator
        $moduleEvent2InvitationGuest1 = new ModuleInvitation();
        $moduleEvent2InvitationGuest1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationGuest1->setTokenEdition($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationGuest1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationGuest1);
        $event2InvitationGuest1->addModuleInvitation($moduleEvent2InvitationGuest1);

        $manager->persist($event2);
        $manager->flush();
    }
}