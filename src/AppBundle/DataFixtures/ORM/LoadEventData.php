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
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Manager\GenerateursToken;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use AppBundle\Utils\enum\ModuleType;
use AppBundle\Utils\enum\PollModuleType;
use AppBundle\Utils\enum\PollModuleVotingType;
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

        $moduleManager = $this->container->get('at.manager.module');
        $moduleInvitationManager = $this->container->get('at.manager.module_invitation');

        $discussionManager = $this->container->get('at.manager.discussion');

        /** @var AccountUser $userprincipal */
        $userprincipal = $userManager->findUserByEmail(LoadUsers::USER_ONE_EMAIL);
        /** @var AccountUser $userInvite */
        $userInvite = $userManager->findUserByEmail(LoadUsers::USER_TWO_EMAIL);

        //-------------//
        // Event 1
        //-------------//
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
        $event1->addEventInvitation($eventInvitationGerard);

        //EventInvitation Invite
        $eventInvitationGertrude = new EventInvitation();
        $eventInvitationGertrude->setGuestName("Gertrude");
        $eventInvitationGertrude->setStatus(EventInvitationStatus::VALID);
        $eventInvitationGertrude->setToken('qsdfgh3');
        $event1->addEventInvitation($eventInvitationGertrude);

        //EventInvitation Invite
        $eventInvitationNobert = new EventInvitation();
        $eventInvitationNobert->setGuestName("Nobert");
        $eventInvitationNobert->setStatus(EventInvitationStatus::VALID);
        $eventInvitationNobert->setToken('qsdfgh4');
        $event1->addEventInvitation($eventInvitationNobert);

        //EventInvitation Invite
        $eventInvitationSimon = new EventInvitation();
        $eventInvitationSimon->setGuestName("Simon");
        $eventInvitationSimon->setStatus(EventInvitationStatus::VALID);
        $eventInvitationSimon->setToken('qsdfgh5');
        $event1->addEventInvitation($eventInvitationSimon);

        //EventInvitation Invite
        $eventInvitationTyrion = new EventInvitation();
        $eventInvitationTyrion->setGuestName("Tyrion");
        $eventInvitationTyrion->setStatus(EventInvitationStatus::VALID);
        $eventInvitationTyrion->setToken('qsdfgh6');
        $event1->addEventInvitation($eventInvitationTyrion);

        //EventInvitation Invite
        $eventInvitationPikachu = new EventInvitation();
        $eventInvitationPikachu->setGuestName("Pikachu");
        $eventInvitationPikachu->setStatus(EventInvitationStatus::VALID);
        $eventInvitationPikachu->setToken('qsdfgh7');
        $event1->addEventInvitation($eventInvitationPikachu);

        //EventInvitation Invite
        $eventInvitationJeanPaul = new EventInvitation();
        $eventInvitationJeanPaul->setGuestName("Jean Paul");
        $eventInvitationJeanPaul->setStatus(EventInvitationStatus::VALID);
        $eventInvitationJeanPaul->setToken('qsdfgh8');
        $event1->addEventInvitation($eventInvitationJeanPaul);

        //EventInvitation Invite
        $eventInvitationTeddy = new EventInvitation();
        $eventInvitationTeddy->setGuestName("Teddy");
        $eventInvitationTeddy->setStatus(EventInvitationStatus::VALID);
        $eventInvitationTeddy->setToken('qsdfgh9');
        $event1->addEventInvitation($eventInvitationTeddy);


        //-------------//
        // Module Poll
        //-------------//
        $modulePoll = $moduleManager->createModule($event1, ModuleType::POLL_MODULE, PollModuleType::WHAT, $eventInvitationCreator);
        $moduleInvitationManager->initializeModuleInvitationsForEvent($event1, $modulePoll);


        $pollModule = $modulePoll->getPollModule();

        $pollProposal1 = new PollProposal();
        $pollProposal1->setDescription("Ca va être sportif!!!");
        $pollProposal1Act1 = new PollProposalElement();
        $pollProposal1Act1->setValString("Match de rugby");
        $pollModule->getPollElements()->first()->addPollProposalElement($pollProposal1Act1);
        $pollProposal1->addPollProposalElement($pollProposal1Act1);
        $pollModule->addPollProposal($pollProposal1);

        $pollProposal2 = new PollProposal();
        $pollProposal2->setDescription("Ca va être ludique");
        $pollProposal2Act1 = new PollProposalElement();
        $pollProposal2Act1->setValString("Carcassonne");
        $pollModule->getPollElements()->first()->addPollProposalElement($pollProposal2Act1);
        $pollProposal2->addPollProposalElement($pollProposal2Act1);
        $pollModule->addPollProposal($pollProposal2);

        $manager->persist($event1);
        $manager->flush();

        // Discussion creation
        $discussionManager->createCommentableThread($event1);
        $discussionManager->createCommentableThread($modulePoll);

        //-------------//
        //Event 2
        //-------------//
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
        $moduleEvent2 = $moduleManager->createModule($event2, ModuleType::POLL_MODULE, PollModuleType::WHAT, $event2InvitationCreator);
        $moduleInvitationManager->initializeModuleInvitationsForEvent($event2, $moduleEvent2);

        //EventInvitation Guest 1
        $event2InvitationGuest1 = new EventInvitation();
        $event2InvitationGuest1->setGuestName("Guest anon. 1");
        $event2InvitationGuest1->setStatus(EventInvitationStatus::VALID);
        $event2InvitationGuest1->setToken('guest1');
        $event2->addEventInvitation($event2InvitationGuest1);

        // Module Event 2 Invitation Creator
        $moduleEvent2InvitationGuest1 = new ModuleInvitation();
        $moduleEvent2InvitationGuest1->setModule($moduleEvent2);
        $moduleEvent2InvitationGuest1->setToken($tokenManager->random(GenerateursToken::TOKEN_LONGUEUR));
        $moduleEvent2InvitationGuest1->setStatus(ModuleInvitationStatus::VALID);
        $moduleEvent2->addModuleInvitation($moduleEvent2InvitationGuest1);
        $event2InvitationGuest1->addModuleInvitation($moduleEvent2InvitationGuest1);

        $manager->persist($event2);
        $manager->flush();

        $discussionManager->createCommentableThread($event2);
        $discussionManager->createCommentableThread($moduleEvent2);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}