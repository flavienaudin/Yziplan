<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Form\Event\EventType;
use AppBundle\Form\Event\InvitationsType;
use AppBundle\Security\ModuleVoter;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var ModuleManager */
    private $moduleManager;

    /** @var PollProposalManager */
    private $pollProposalManager;

    /** @var  EventInvitationManager */
    private $eventInvitationManager;

    /** @var ModuleInvitationManager */
    private $moduleInvitationManager;

    /** @var DiscussionManager $discussionManager */
    private $discussionManager;

    /** @var Event L'événement en cours de traitement */
    private $event;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactory $formFactory,
                                GenerateursToken $generateurToken, ModuleManager $moduleManager, PollProposalManager $pollProposalManager, EventInvitationManager $eventInvitationManager,
                                ModuleInvitationManager $moduleInvitationManager, DiscussionManager $discussionManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->moduleManager = $moduleManager;
        $this->pollProposalManager = $pollProposalManager;
        $this->eventInvitationManager = $eventInvitationManager;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->discussionManager = $discussionManager;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return EventManager
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @param $token string Le token de l'événement à récupérer
     * @return Event|null L'événement trouvé sinon null
     */
    public function retrieveEvent($token)
    {
        if (empty($token)) {
            $this->initializeEvent(true);
        } else {
            $eventRep = $this->entityManager->getRepository(Event::class);
            $this->event = $eventRep->findOneBy(array('token' => $token));
        }
        return $this->event;
    }

    /**
     * @param $create boolean If true then an event is create
     * @return Event l'événement initialisé en cours de création
     */
    public function initializeEvent($create = false)
    {
        if ($create) {
            $this->event = new Event();
        }
        if ($this->event->getStatus() == null) {
            $this->event->setStatus(EventStatus::IN_CREATION);
        }
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        $user = $this->tokenStorage->getToken()->getUser();
        $this->eventInvitationManager->createCreatorEventInvitation($this->event, ($user instanceof AccountUser ? $user->getApplicationUser() : null));

        if (empty($this->getEvent()->getId())) {
            $this->entityManager->persist($this->getEvent());
            $this->entityManager->flush();
        }
        return $this->event;
    }

    /**
     * @return Event The Event updated
     */
    public function persistEvent()
    {
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();
        return $this->event;
    }

    /**
     * @return FormInterface Formulaire de création/édition d'un événement
     */
    public function initEventForm()
    {
        return $this->formFactory->create(EventType::class, $this->event);
    }


    /**
     * Renseigne l'événement à partir des données soumises dans le formulaire lors d'une création ou édition
     * @param Form $evtForm
     * @return Event|mixed
     */
    public function treatEventFormSubmission(Form $evtForm)
    {
        $this->event = $evtForm->getData();
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if ($this->event->getStatus() == EventStatus::IN_CREATION) {
            $this->event->setStatus(EventStatus::IN_ORGANIZATION);
        }
        if (empty($this->event->getWhereName())) {
            $this->event->setWhereGooglePlaceId(null);
        }
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->event;
    }

    /**
     * Duplique l'événement passé en paramètre (ou celui en cours) en fonction des paramètres : dupliquer les mdules et/ou les invitations
     * L'événement n'est pas persisté en base de données
     *
     * @param boolean $duplicateModules Les modules sont également dupliqué et associés au nouvel événement
     * @param boolean $duplicateInvitations Les invitations existantes sont utilisées pour les invitations du nouvel événement
     * @param Event|null $event L'événement à duppliquer (si non donné,  l'attribut de la classe est utilisé)
     * @return Event Résultat de la duplication de l'événement
     */
    public function duplicateEvent($duplicateModules, Event $event = null)
    {
        if ($event != null) {
            $this->event = $event;
        }
        if ($this->event != null) {
            $duplicatedEvent = new Event();
            $duplicatedEvent->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
            $duplicatedEvent->setStatus(EventStatus::IN_ORGANIZATION);
            $duplicatedEvent->setName($this->event->getName());
            $duplicatedEvent->setDescription($this->event->getDescription());

            $duplicatedEvent->setWhereName($this->event->getWhereName());
            $duplicatedEvent->setWhereGooglePlaceId($this->event->getWhereGooglePlaceId());
            $duplicatedEvent->setWhen($this->event->getWhen());

            $duplicatedEvent->setInvitationOnly($this->event->isInvitationOnly());
            $duplicatedEvent->setGuestsCanInvite($this->event->isGuestsCanInvite());
            $duplicatedEvent->setGuestsCanAddModule($this->event->isGuestsCanAddModule());

            if ($duplicateModules) {
                /** @var Module $originalModule */
                foreach ($event->getModules() as $originalModule) {
                    if ($originalModule->getStatus() == ModuleStatus::IN_ORGANIZATION) {
                        /** @var Module $duplicatedModule */
                        $duplicatedModule = $this->moduleManager->duplicateModule($originalModule);
                        $duplicatedEvent->addModule($duplicatedModule);
                    }
                }
            }
            // TODO : duplicate EventInvitation ?
            return $duplicatedEvent;
        } else {
            return null;
        }
    }

    public function createEventInvitationsForm()
    {
        return $this->formFactory->create(InvitationsType::class);
    }

    public function treatEventInvitationsFormSubmission(FormInterface $eventInvitationsForm)
    {
        if ($this->event != null) {
            $emailsData = $eventInvitationsForm->get("invitations")->getData();
            $this->eventInvitationManager->sendInvitations($this->event, $emailsData);
            $this->entityManager->persist($this->event);
            $this->entityManager->flush();
            return $this->event;
        }
        return null;
    }

    /**
     * Crée et ajoute un module à l'événement
     * @param $type string Le type du module à créer et à ajouter à l'événement (Cf. ModuleType)
     * @param $type string Le sous-type du module en fonction du type (Cf. PollModuleType et autre)
     * @return Module le module créé
     */
    public function addModule($type, $subtype)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof AccountUser) {
            $user = null;
        }
        $userEventInvitation = $this->eventInvitationManager->retrieveUserEventInvitation($this->event, false, false, $user);
        if ($userEventInvitation == null) {
            return null;
        }

        $module = $this->moduleManager->createModule($this->event, $type, $subtype, $userEventInvitation);

        // TODO Implémenter un controle des moduleInvitaiton créé (liste d'invité, droit, définissable par le module.creator).
        $this->moduleInvitationManager->initializeModuleInvitationsForEvent($this->event, $module);
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();


        // Create the thread after the module affectation to its event because of the thread ID is generate with the event token
        $this->discussionManager->createCommentableThread($module);

        return $module;
    }

    /**
     * @param EventInvitation $userEventInvitation
     * @param string $requestUri The URI of the request to create thread for modules
     * @return array Un tableau de modules de l'événement au format :
     *  moduleId => [
     *  'module' => Module : Le module lui-meme
     *  'moduleForm' => Form : le formulaire d'édition de l'événement si editable
     *  'pollProposalAddForm' => Form : uniquement pour un PollModule
     * ]
     */
    public function getModulesToDisplay(EventInvitation $userEventInvitation)
    {
        $modules = array();
        if ($this->event != null) {
            $eventModules = $this->entityManager->getRepository(Module::class)->findOrderedByEventToken($this->event->getToken());
            /** @var Module $module */
            foreach ($eventModules as $module) {
                if ($module->getStatus() != ModuleStatus::DELETED && $module->getStatus() != ModuleStatus::ARCHIVED) {
                    $moduleDescription = array();
                    $moduleDescription['module'] = $module;
                    $userModuleInvitation = $userEventInvitation->getModuleInvitationForModule($module);

                    $thread = $module->getCommentThread();
                    if (null == $thread) {
                        $thread = $this->discussionManager->createCommentableThread($module);
                    }
                    $comments = $this->discussionManager->getCommentsThread($thread);
                    $moduleDescription['thread'] = $thread;
                    $moduleDescription['comments'] = $comments;

                    if ($this->authorizationChecker->isGranted(ModuleVoter::EDIT, $userModuleInvitation)) {
                        $moduleDescription['moduleForm'] = $this->moduleManager->createModuleForm($module);
                    }
                    if ($module->getPollModule() != null) {
                        // TODO Vérifier les autorisations d'ajouter des propositions au module
                        $moduleDescription['pollProposalAddForm'] = $this->pollProposalManager->createPollProposalAddForm($module->getPollModule(), $userModuleInvitation);
                    }
                    $modules[$module->getId()] = $moduleDescription;
                }
            }
        }
        return $modules;
    }


}