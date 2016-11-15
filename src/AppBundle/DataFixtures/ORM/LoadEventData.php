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
use AppBundle\Entity\Module\PollElement;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Manager\GenerateursToken;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\PollElementType;
use AppBundle\Utils\enum\PollModuleSortingType;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEventData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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

        /** @var AccountUser $userprincipal */
        $userprincipal = $userManager->findUserByEmail(LoadUsers::USER_ONE_EMAIL);
        /** @var AccountUser $userInvite */
        $userInvite = $userManager->findUserByEmail(LoadUsers::USER_TWO_EMAIL);

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

        //EventInvitation Invite
        $eventInvitationGerard = new EventInvitation();
        $eventInvitationGerard->setGuestName("Gerard");
        $eventInvitationGerard->setStatus(EventInvitationStatus::VALID);
        $eventInvitationGerard->setToken('qsdfgh2');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationGerard);
        $event1->addEventInvitation($eventInvitationGerard);

        //EventInvitation Invite
        $eventInvitationGertrude = new EventInvitation();
        $eventInvitationGertrude->setGuestName("Gertrude");
        $eventInvitationGertrude->setStatus(EventInvitationStatus::VALID);
        $eventInvitationGertrude->setToken('qsdfgh3');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationGertrude);
        $event1->addEventInvitation($eventInvitationGertrude);

        //EventInvitation Invite
        $eventInvitationNobert = new EventInvitation();
        $eventInvitationNobert->setGuestName("Nobert");
        $eventInvitationNobert->setStatus(EventInvitationStatus::VALID);
        $eventInvitationNobert->setToken('qsdfgh4');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationNobert);
        $event1->addEventInvitation($eventInvitationNobert);

        //EventInvitation Invite
        $eventInvitationSimon = new EventInvitation();
        $eventInvitationSimon->setGuestName("Simon");
        $eventInvitationSimon->setStatus(EventInvitationStatus::VALID);
        $eventInvitationSimon->setToken('qsdfgh5');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationSimon);
        $event1->addEventInvitation($eventInvitationSimon);

        //EventInvitation Invite
        $eventInvitationTyrion = new EventInvitation();
        $eventInvitationTyrion->setGuestName("Tyrion");
        $eventInvitationTyrion->setStatus(EventInvitationStatus::VALID);
        $eventInvitationTyrion->setToken('qsdfgh6');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationTyrion);
        $event1->addEventInvitation($eventInvitationTyrion);

        //EventInvitation Invite
        $eventInvitationPikachu = new EventInvitation();
        $eventInvitationPikachu->setGuestName("Pikachu");
        $eventInvitationPikachu->setStatus(EventInvitationStatus::VALID);
        $eventInvitationPikachu->setToken('qsdfgh7');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationPikachu);
        $event1->addEventInvitation($eventInvitationPikachu);

        //EventInvitation Invite
        $eventInvitationJeanPaul = new EventInvitation();
        $eventInvitationJeanPaul->setGuestName("Jean Paul");
        $eventInvitationJeanPaul->setStatus(EventInvitationStatus::VALID);
        $eventInvitationJeanPaul->setToken('qsdfgh8');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationJeanPaul);
        $event1->addEventInvitation($eventInvitationJeanPaul);

        //EventInvitation Invite
        $eventInvitationTeddy = new EventInvitation();
        $eventInvitationTeddy->setGuestName("Teddy");
        $eventInvitationTeddy->setStatus(EventInvitationStatus::VALID);
        $eventInvitationTeddy->setToken('qsdfgh9');
        $userInvite->getApplicationUser()->addEventInvitation($eventInvitationTeddy);
        $event1->addEventInvitation($eventInvitationTeddy);

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
        $moduleInvitation1->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation1->setCreator(true);
        $moduleInvitation1->initPollModuleResponse();
        $eventInvitationCreator->addModuleInvitation($moduleInvitation1);

        $pollProposal1->setCreator($moduleInvitation1);
        $pollProposal2->setCreator($moduleInvitation1);

        $moduleInvitation2 = new ModuleInvitation();
        $moduleInvitation2->setModule($modulePoll);
        $moduleInvitation2->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation2->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation2->initPollModuleResponse();
        $eventInvitationRaymond->addModuleInvitation($moduleInvitation2);

        $moduleInvitation3 = new ModuleInvitation();
        $moduleInvitation3->setModule($modulePoll);
        $moduleInvitation3->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation3->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation3->initPollModuleResponse();
        $eventInvitationGerard->addModuleInvitation($moduleInvitation3);

        $moduleInvitation4 = new ModuleInvitation();
        $moduleInvitation4->setModule($modulePoll);
        $moduleInvitation4->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation4->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation4->initPollModuleResponse();
        $eventInvitationGertrude->addModuleInvitation($moduleInvitation4);

        $moduleInvitation5 = new ModuleInvitation();
        $moduleInvitation5->setModule($modulePoll);
        $moduleInvitation5->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation5->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation5->initPollModuleResponse();
        $eventInvitationNobert->addModuleInvitation($moduleInvitation5);

        $moduleInvitation6 = new ModuleInvitation();
        $moduleInvitation6->setModule($modulePoll);
        $moduleInvitation6->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation6->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation6->initPollModuleResponse();
        $eventInvitationSimon->addModuleInvitation($moduleInvitation6);

        $moduleInvitation7 = new ModuleInvitation();
        $moduleInvitation7->setModule($modulePoll);
        $moduleInvitation7->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation7->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation7->initPollModuleResponse();
        $eventInvitationTyrion->addModuleInvitation($moduleInvitation7);

        $moduleInvitation8 = new ModuleInvitation();
        $moduleInvitation8->setModule($modulePoll);
        $moduleInvitation8->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation8->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation8->initPollModuleResponse();
        $eventInvitationPikachu->addModuleInvitation($moduleInvitation8);

        $moduleInvitation9 = new ModuleInvitation();
        $moduleInvitation9->setModule($modulePoll);
        $moduleInvitation9->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation9->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation9->initPollModuleResponse();
        $eventInvitationJeanPaul->addModuleInvitation($moduleInvitation9);

        $moduleInvitation10 = new ModuleInvitation();
        $moduleInvitation10->setModule($modulePoll);
        $moduleInvitation10->setStatus(ModuleInvitationStatus::VALID);
        $moduleInvitation10->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleInvitation10->initPollModuleResponse();
        $eventInvitationTeddy->addModuleInvitation($moduleInvitation10);

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
        $moduleEvent2InvitationCreator->setStatus(ModuleInvitationStatus::VALID);
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
        $moduleEvent2InvitationGuest1->setStatus(ModuleInvitationStatus::VALID);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationGuest1);
        $event2InvitationGuest1->addModuleInvitation($moduleEvent2InvitationGuest1);

        $manager->persist($event2);
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}