<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 13/06/2016
 * Time: 10:52
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\enum\PollModuleSortingType;
use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\ExpenseElement;
use AppBundle\Entity\Module\ExpenseModule;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Manager\GenerateursToken;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\PollElementType;
use ATUserBundle\Entity\AccountUser;
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
        $userManager = $this->container->get('at.manager.user');
        $tokenManager = $this->container->get('at.service.gentoken');

        /** @var $userprincipal AccountUser */
        $userprincipal = $userManager->createUser();
        $userprincipal->setUsername("user1@at.fr");
        $userprincipal->setEmail('user1@at.fr');
        $userprincipal->setPlainPassword('user1');
        $userprincipal->setPasswordKnown(true);
        $userprincipal->setEnabled(true);
        // Update the user for password
        $userManager->updateUser($userprincipal, true);


        /** @var $userInvite AccountUser */
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
        $eventInvitationCreator->setGuestName("Jacky");
        $eventInvitationCreator->setStatus(EventInvitationStatus::VALID);
        $eventInvitationCreator->setToken('azerty');
        $eventInvitationCreator->setCreator(true);
        $userprincipal->getApplicationUser()->addEventInvitation($eventInvitationCreator);
        $event->addEventInvitation($eventInvitationCreator);

        //EventInvitation Invite
        $eventInvitationRaymond = new EventInvitation();
        $eventInvitationRaymond->setGuestName("Raymond");
        $eventInvitationRaymond->setStatus(EventInvitationStatus::VALID);
        $eventInvitationRaymond->setToken('qsdfgh');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationRaymond);
        $event->addEventInvitation($eventInvitationRaymond);

        //Module
        $moduleExpense = new Module();
        $event->addModule($moduleExpense);
        $moduleExpense->setName("Test Expense Module");
        $moduleExpense->setDescription("Ceci est une super description.

        Avec un saut à la ligne!");
        $moduleExpense->setToken("12345");
        $moduleExpense->setStatus(EventStatus::IN_ORGANIZATION);
        $moduleExpense->setOrderIndex(2);

       //Module specifique pour les depenses
        $expenseModule = new ExpenseModule();
        $moduleExpense->setExpenseModule($expenseModule);

        $expenseProposal = new ExpenseElement();
        $expenseProposal->setName("Restaurant");
        $expenseProposal->setAmount(12);
        $expenseProposal->setExpenseDate(new \DateTime());
        $expenseProposal->setCreator($eventInvitationCreator);
        $expenseProposal->setPayer($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitationCreator);
        $expenseProposal->addListOfParticipant($eventInvitationRaymond);
        $expenseModule->addExpenseElement($expenseProposal);
        
        $expenseProposal2 = new ExpenseElement();
        $expenseProposal2->setName("Restaurant2");
        $expenseProposal2->setAmount(36);
        $expenseProposal2->setExpenseDate(new \DateTime());
        $expenseProposal2->setCreator($eventInvitationRaymond);
        $expenseProposal2->setPayer($eventInvitationRaymond);
        $expenseProposal2->addListOfParticipant($eventInvitationCreator);
        $expenseProposal2->addListOfParticipant($eventInvitationRaymond);
        $expenseModule->addExpenseElement($expenseProposal2);

        $expenseModuleInvitation1 = new ModuleInvitation();
        $expenseModuleInvitation1->setModule($moduleExpense);
        $expenseModuleInvitation1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $expenseModuleInvitation1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $eventInvitationCreator->addModuleInvitation($expenseModuleInvitation1);

        $expenseModuleInvitation2 = new ModuleInvitation();
        $expenseModuleInvitation2->setModule($moduleExpense);
        $expenseModuleInvitation2->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $expenseModuleInvitation2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $expenseModuleInvitation2->isCreator();
        $eventInvitationRaymond->addModuleInvitation($expenseModuleInvitation2);


        // Module Poll
        $modulePoll = new Module();
        $event->addModule($modulePoll);
        $modulePoll->setName("Module Quand");
        $modulePoll->setDescription("C'est un chouette module");
        $modulePoll->setToken("quand123");
        $modulePoll->setStatus(EventStatus::IN_ORGANIZATION);
        $modulePoll->setOrderIndex(1);

        $pollModule = new PollModule();
        $pollModule->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
        $modulePoll->setPollModule($pollModule);

        $pollProposal1 = new PollProposal();
        $pollProposalElement1 = new PollProposalElement();
        $pollProposalElement1->setName("Occasion");
        $pollProposalElement1->setType(PollElementType::STRING);
        $pollProposalElement1->setValString("Crémaillère");
        $pollProposal1->addPollProposalElement($pollProposalElement1);
        $pollProposalElement2 = new PollProposalElement();
        $pollProposalElement2->setName("Complt.");
        $pollProposalElement2->setType(PollElementType::STRING);
        $pollProposalElement2->setValString("Party");
        $pollProposal1->addPollProposalElement($pollProposalElement2);
        $pollModule->addPollProposal($pollProposal1);

        $pollProposal2 = new PollProposal();
        $pollProposalElement11 = new PollProposalElement();
        $pollProposalElement11->setName("Occasion");
        $pollProposalElement11->setType(PollElementType::STRING);
        $pollProposalElement11->setValString("Anniversaire");
        $pollProposal2->addPollProposalElement($pollProposalElement11);
        $pollProposalElement22 = new PollProposalElement();
        $pollProposalElement22->setName("Complt.");
        $pollProposalElement22->setType(PollElementType::STRING);
        $pollProposalElement22->setValString("30 ans");
        $pollProposal2->addPollProposalElement($pollProposalElement22);
        $pollModule->addPollProposal($pollProposal2);

        $moduleInvitation1 = new ModuleInvitation();
        $moduleInvitation1->setModule($modulePoll);
        $moduleInvitation1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleInvitation1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation1->setCreator(true);
        $moduleInvitation1->initPollModuleResponse();
        $eventInvitationCreator->addModuleInvitation($moduleInvitation1);

        $pollProposal1->setCreator($moduleInvitation1);
        $pollProposal2->setCreator($moduleInvitation1);

        $moduleInvitation2 = new ModuleInvitation();
        $moduleInvitation2->setModule($modulePoll);
        $moduleInvitation2->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleInvitation2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
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
        $event2InvitationCreator->setGuestName("Anon. 1");
        $event2InvitationCreator->setStatus(EventInvitationStatus::VALID);
        $event2InvitationCreator->setToken('0azerty');
        $event2InvitationCreator->setCreator(true);
        $event2->addEventInvitation($event2InvitationCreator);

        // Module Event 2
        $moduleEvent2 = new Module();
        $moduleEvent2->setStatus(ModuleStatus::IN_CREATION);
        $moduleEvent2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $pollModuleEvent2 = new PollModule();
        $pollModuleEvent2->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
        $moduleEvent2->setPollModule($pollModuleEvent2);
        $event2->addModule($moduleEvent2);

        // Module Event 2 Invitation Creator
        $moduleEvent2InvitationCreator = new ModuleInvitation();
        $moduleEvent2InvitationCreator->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationCreator->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationCreator);
        $moduleEvent2InvitationCreator->isCreator();
        $event2InvitationCreator->addModuleInvitation($moduleEvent2InvitationCreator);

        //EventInvitation Guest 1
        $event2InvitationGuest1 = new EventInvitation();
        $event2InvitationGuest1->setGuestName("Guest anon. 1");
        $event2InvitationGuest1->setStatus(EventInvitationStatus::VALID);
        $event2InvitationGuest1->setToken('guest1');
        $event2->addEventInvitation($event2InvitationGuest1);

        // Module Event 2 Invitation Creator
        $moduleEvent2InvitationGuest1 = new ModuleInvitation();
        $moduleEvent2InvitationGuest1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationGuest1->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationGuest1);
        $event2InvitationGuest1->addModuleInvitation($moduleEvent2InvitationGuest1);

        $manager->persist($event2);
        $manager->flush();
    }
}