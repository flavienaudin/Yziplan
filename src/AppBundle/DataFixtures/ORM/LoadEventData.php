<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 13/06/2016
 * Time: 10:52
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\ExpenseElement;
use AppBundle\Entity\Module\ExpenseModule;
use AppBundle\Entity\Module\PollElement;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Manager\GenerateursToken;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\PollElementType;
use AppBundle\Utils\enum\PollModuleSortingType;
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
        $userprincipalAppEmail = new AppUserEmail();
        $userprincipalAppEmail->setEmail("user1@at.fr");
        $userprincipal->getApplicationUser()->addAppUserEmail($userprincipalAppEmail);
        $userprincipal->getApplicationUser()->getAppUserInformation()->setPublicName("User One");
        // Update the user for password
        $userManager->updateUser($userprincipal, true);


        /** @var $userInvite AccountUser */
        $userInvite = $userManager->createUser();
        $userInvite->setUsername('user2@at.fr');
        $userInvite->setEmail('user2@at.fr');
        $userInvite->setPlainPassword('user2');
        $userInvite->setPasswordKnown(true);
        $userInvite->setEnabled(true);
        $userInviteAppEmail = new AppUserEmail();
        $userInviteAppEmail->setEmail("user2@at.fr");
        $userInvite->getApplicationUser()->addAppUserEmail($userInviteAppEmail);
        $userInvite->getApplicationUser()->getAppUserInformation()->setPublicName("User two");
        // Update the user for password
        $userManager->updateUser($userInvite, true);

        //Event
        $event1 = new Event();
        $event1->setName("Evenement de la soirée");
        $event1->setToken("123456789");
        $event1->setStatus(EventStatus::IN_ORGANIZATION);
        $event1->setDescription("On se fait une soirée. on va s'éclater");
        $event1->setInvitationOnly(false);
        $event1->setGuestsCanInvite(true);
        $event1->setGuestsCanAddModule(true);

        //EventInvitation Creator
        $eventInvitationCreator = new EventInvitation();
        $eventInvitationCreator->setGuestName("Jacky");
        $eventInvitationCreator->setStatus(EventInvitationStatus::VALID);
        $eventInvitationCreator->setToken('azerty');
        $eventInvitationCreator->setCreator(true);
        $userprincipal->getApplicationUser()->addEventInvitation($eventInvitationCreator);
        $event1->addEventInvitation($eventInvitationCreator);

        //EventInvitation Invite
        $eventInvitationRaymond = new EventInvitation();
        $eventInvitationRaymond->setGuestName("Raymond");
        $eventInvitationRaymond->setStatus(EventInvitationStatus::VALID);
        $eventInvitationRaymond->setToken('qsdfgh');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationRaymond);
        $event1->addEventInvitation($eventInvitationRaymond);

        /*
        //Module Expense
        $moduleExpense = new Module();
        $event1->addModule($moduleExpense);
        $moduleExpense->setName("Test Expense Module");
        $moduleExpense->setDescription("Ceci est une super description.

        Avec un saut à la ligne!");
        $moduleExpense->setToken("12345");
        $moduleExpense->setStatus(EventStatus::IN_ORGANIZATION);
        $moduleExpense->setOrderIndex(2);

       //Module specifique pour les depenses
        $expenseModule = new ExpenseModule();
        $moduleExpense->setExpenseModule($expenseModule);

        $expenseElement = new ExpenseElement();
        $expenseElement->setName("Restaurant");
        $expenseElement->setAmount(12);
        $expenseElement->setExpenseDate(new \DateTime());
        $expenseElement->setCreator($eventInvitationCreator);
        $expenseElement->setPayer($eventInvitationCreator);
        $expenseElement->addListOfParticipant($eventInvitationCreator);
        $expenseElement->addListOfParticipant($eventInvitationRaymond);
        $expenseModule->addExpenseElement($expenseElement);
        
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
        */


        // Module Poll
        $modulePoll = new Module();
        $event1->addModule($modulePoll);
        $modulePoll->setName("Quoi qu'on fait ?");
        $modulePoll->setDescription("On veut savoir ce qu'on fait");
        $modulePoll->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $modulePoll->setStatus(ModuleStatus::IN_ORGANIZATION);
        $modulePoll->setOrderIndex(1);

        $pollModule = new PollModule();
        $pollModule->setSortingType(PollModuleSortingType::YES_NO_MAYBE);
        $modulePoll->setPollModule($pollModule);

        $pollElementAct1 = new PollElement();
        $pollModule->addPollElement($pollElementAct1);
        $pollElementAct1->setName("Activité 1");
        $pollElementAct1->setType(PollElementType::STRING);
        $pollElementAct1->setOrderIndex(1);

        $pollElementAct2 = new PollElement();
        $pollModule->addPollElement($pollElementAct2);
        $pollElementAct2->setName("Activité 2");
        $pollElementAct2->setType(PollElementType::STRING);
        $pollElementAct2->setOrderIndex(2);

        $pollProposal1 = new PollProposal();
        $pollProposal1->setDescription("Ca va être sportif!!!");
        $pollProposal1Act1 = new PollProposalElement();
        $pollProposal1Act1->setValString("Match de rugby");
        $pollElementAct1->addPollProposalElement($pollProposal1Act1);
        $pollProposal1->addPollProposalElement($pollProposal1Act1);
        $pollProposal1Act2 = new PollProposalElement();
        $pollProposal1Act2->setValString("3ième mi-temps");
        $pollElementAct2->addPollProposalElement($pollProposal1Act2);
        $pollProposal1->addPollProposalElement($pollProposal1Act2);
        $pollModule->addPollProposal($pollProposal1);

        $pollProposal2 = new PollProposal();
        $pollProposal2->setDescription("Ca va être ludique");
        $pollProposal2Act1 = new PollProposalElement();
        $pollProposal2Act1->setValString("Carcassonne");
        $pollElementAct2->addPollProposalElement($pollProposal2Act1);
        $pollProposal2->addPollProposalElement($pollProposal2Act1);
        $pollProposal2Act2 = new PollProposalElement();
        $pollProposal2Act2->setValString("7 wxonders");
        $pollElementAct2->addPollProposalElement($pollProposal2Act2);
        $pollProposal2->addPollProposalElement($pollProposal2Act2);
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

        $manager->persist($event1);
        $manager->flush();

        //Event 2
        $event2 = new Event();
        $event2->setName("Evénement 2");
        $event2->setToken("012345678");
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