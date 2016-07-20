<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;

use AppBundle\Entity\enum\EventStatus;
use AppBundle\Entity\enum\ModuleStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\Module;
use AppBundle\Form\EventFormType;
use AppBundle\Security\ModuleVoter;
use ATUserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
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

    /** @var  EventInvitationManager */
    private $eventInvitationManager;

    /** @var Event L'événement en cours de traitement */
    private $event;

    public function __construct(EntityManager $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, FormFactory $formFactory, GenerateursToken $generateurToken, ModuleManager $moduleManager, EventInvitationManager $eventInvitationManager)
    {
        $this->entityManager = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->moduleManager = $moduleManager;
        $this->eventInvitationManager = $eventInvitationManager;
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
            $eventRep = $this->entityManager->getRepository("AppBundle:Event");
            $this->event = $eventRep->findOneBy(array('token' => $token));
        }
        return ($this->event instanceof Event ? $this->event : null);
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
        if (empty($this->event->getTokenEdition())) {
            $this->event->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ($this->eventInvitationManager->createCreatorEventInvitation($this->event, ($user instanceof User ? $user : null)) == null) {
            $this->event = null;
            return $this->event;
        }

        if (empty($this->getEvent()->getId())) {
            $this->entityManager->persist($this->getEvent());
            $this->entityManager->flush();
        }
        return $this->event;
    }

    /**
     * @return Form Formulaire de création/édition d'un événement
     */
    public function initEventForm()
    {
        return $this->formFactory->create(EventFormType::class, $this->event);
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
        if (empty($this->event->getTokenEdition())) {
            $this->event->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }

        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->event;
    }

    /**
     * Crée et ajoute un module à l'événement
     * @param $type string Le type du module à créer et à ajouter à l'événement
     * @return Module le module créé
     */
    public function addModule($type)
    {
        $module = $this->moduleManager->createModule($type);
        $this->event->addModule($module);

        // TODO Créer les moduleInvitations selon autorisation

        $this->entityManager->persist($this->event);
        $this->entityManager->flush();
        return $module;
    }

    /**
     * @return array Un tableau de modules de l'événement au format :
     *  moduleId => [
     *  'module' => Module : Le module lui-meme
     *  'moduleForm' => Form : le formulaire d'édition de l'événement si editable
     *  'addPollProposalForm' => Form : uniquement pour un PollModule
     * ]
     */
    public function getModulesToDisplay()
    {
        $modules = array();
        if ($this->event != null) {
            $eventModules = $this->entityManager->getRepository("AppBundle:Module")->findOrderedByEventToken($this->event->getToken());
            /** @var Module $module */
            foreach ($eventModules as $module) {
                if ($module->getStatus() != ModuleStatus::DELETED && $module->getStatus() != ModuleStatus::ARCHIVED) {
                    $moduleDescription['module'] = $module;
                    if ($this->authorizationChecker->isGranted(ModuleVoter::EDIT, $module)) {
                        $moduleDescription['moduleForm'] = $this->moduleManager->createModuleForm($module);
                    }
                    if ($module->getPollModule() != null) {
                        // TODO Vérifier les autorisations d'ajouter des propositions au module
                        $moduleDescription['addPollProposalForm'] = $this->moduleManager->createAddPollProposalForm($module);
                    }
                    $modules[$module->getId()] = $moduleDescription;
                }
            }
        }
        return $modules;
    }


}