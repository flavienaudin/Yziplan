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
use AppBundle\Form\Event\EventTemplateSettingsType;
use AppBundle\Form\Event\EventType;
use AppBundle\Form\Event\InvitationsType;
use AppBundle\Form\Event\SendMessageType;
use AppBundle\Form\Module\PollProposalCollectionType;
use AppBundle\Security\ModuleVoter;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\ModuleStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    /** @var ArrayCollection Les OpeninghOurs de l'évnément pour mise à jour */
    private $eventOriginalOpeningHours;

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
        // Create an ArrayCollection of the current OpeningHour objects in the database
        $this->eventOriginalOpeningHours = new ArrayCollection();
        foreach ($this->event->getOpeningHours() as $openingHour) {
            $this->eventOriginalOpeningHours->add($openingHour);
        }
        return $this->formFactory->create(EventType::class, $this->event);
    }

    /**
     * Renseigne l'événement à partir des données soumises dans le formulaire lors d'une création ou édition
     * @param FormInterface $evtForm
     * @return Event|mixed
     */
    public function treatEventFormSubmission(FormInterface $evtForm)
    {
        $this->event = $evtForm->getData();
        if (empty($this->event->getToken())) {
            $this->event->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        }
        if ($this->event->getStatus() == EventStatus::IN_CREATION) {
            $this->event->setStatus(EventStatus::IN_ORGANIZATION);
        }
        /* TODO : desactivés pour simplifier l'interface */
        $this->event->setInvitationOnly(false);
        $this->event->setGuestsCanInvite(true);
        if (empty($this->event->getWhereName())) {
            $this->event->setWhereGooglePlaceId(null);
        }

        // remove the relationship between the OpeningHours and the Event
        foreach ($this->eventOriginalOpeningHours as $openingHour) {
            if (false === $this->event->getOpeningHours()->contains($openingHour)) {
                // remove the OpeningHours from the Event
                $this->event->removeOpeningHour($openingHour);
                $this->entityManager->remove($openingHour);
            }
        }
        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->event;
    }

    /**
     * Duplique l'événement passé en paramètre (ou celui en cours). LEs modules sont dupliqués ou non selon le paramètre $duplicateModules
     * L'événement n'est pas persisté en base de données
     *
     * @param boolean $duplicateModules Les modules sont également dupliqués et associés au nouvel événement
     * @param Event|null $event L'événement à dupliquer (si non donné,  l'attribut "event" de la classe est utilisé)
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
            $duplicatedEvent->setResponseDeadline($this->event->getResponseDeadline());

            if ($this->event->getPictureFile() != null) {
                $originalFile = $this->event->getPictureFile();
                $tempFileCopyName = str_replace($originalFile->getExtension(), 'bak.' . $originalFile->getExtension(), $originalFile->getFilename());
                $tempFileCopyPathname = $this->event->getPictureFile()->getPath() . '/' . $tempFileCopyName;
                if (copy($this->event->getPictureFile()->getPathname(), $tempFileCopyPathname)) {
                    $newFile = new UploadedFile($tempFileCopyPathname, $tempFileCopyName, $originalFile->getMimeType(), $originalFile->getSize(), null, true);
                    $duplicatedEvent->setPictureFile($newFile);

                    $duplicatedEvent->setPictureFocusX($this->event->getPictureFocusX());
                    $duplicatedEvent->setPictureFocusY($this->event->getPictureFocusY());
                    $duplicatedEvent->setPictureWidth($this->event->getPictureWidth());
                    $duplicatedEvent->setPictureHeight($this->event->getPictureHeight());
                }
            }

            $duplicatedEvent->setWhereName($this->event->getWhereName());
            $duplicatedEvent->setWhereGooglePlaceId($this->event->getWhereGooglePlaceId());
            $duplicatedEvent->setWhen($this->event->getWhen());

            if ($this->event->getCoordinates() != null) {
                $this->event->getCoordinates()->duplicate($duplicatedEvent);
            }
            foreach ($this->event->getOpeningHours() as $openingHour) {
                $openingHour->duplicate($duplicatedEvent);
            }

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
            return $duplicatedEvent;
        } else {
            return null;
        }
    }

    public function createEventInvitationsForm()
    {
        return $this->formFactory->create(InvitationsType::class);
    }

    public function treatEventInvitationsFormSubmission(FormInterface $eventInvitationsForm, $sendInvitations = true, &$resultCreation = array())
    {
        if ($this->event != null) {
            $emailsData = $eventInvitationsForm->get("invitations")->getData();
            if ($eventInvitationsForm->has('message')) {
                $message = $eventInvitationsForm->get('message')->getData();
            } else {
                $message = null;
            }
            $resultCreation = $this->eventInvitationManager->createInvitations($this->event, $emailsData, $sendInvitations, $message);

            $this->entityManager->persist($this->event);
            $this->entityManager->flush();
            return $this->event;
        }
        return null;
    }

    /**
     * Génère le formulaire pour envoyer un rappel aux invités
     *
     * @return FormInterface
     */
    public function createSendMessageForm()
    {
        return $this->formFactory->create(SendMessageType::class);
    }

    /**
     * Traite la soumission du formulaire d'envoie d'un message aux invités
     * @param Form $sendMessageForm
     * @param EventInvitation $userEventInvitation L'invitation de l'utilisateur connecté qui envoye le message
     * @return false|array : false if error, array of failed recipients
     */
    public function treatSendMessageFormSubmission(FormInterface $sendMessageForm, EventInvitation $userEventInvitation)
    {
        if ($this->event != null) {
            $message = $sendMessageForm->get("message")->getData();
            $selection = $sendMessageForm->get("selection")->getData();
            if ($selection == null || !is_array($selection)) {
                $selection = array();
            }
            $recipients = $this->event->getEventInvitationByAnswer($selection);
            if ($recipients->contains($userEventInvitation)) {
                // On n'envoit pas le message à l'expéditeur
                $recipients->removeElement($userEventInvitation);
            }
            $failedRecipients = array();
            $this->eventInvitationManager->sendMessage($recipients, $message, $failedRecipients);
            return $failedRecipients;
        } else {
            return false;
        }
    }

    /**
     * Génère le formulaire de configuration de la duplication de type Template
     * @return FormInterface
     */
    public function createTemplateSettingsForm()
    {
        return $this->formFactory->create(EventTemplateSettingsType::class);
    }

    /**
     * Traite la soumission du formulaire de configuration de la duplication de type Template
     * @param FormInterface $templateSettingsForm
     * @return Event
     */
    public function treatTemplateSettingsForm(FormInterface $templateSettingsForm)
    {
        if ($templateSettingsForm->get("activateTemplate")->getData()) {
            if (empty($this->event->getTokenDuplication())) {
                $this->event->setTokenDuplication($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
                $this->persistEvent();
            }
        } else {
            $this->event->setTokenDuplication(null);
            $this->persistEvent();
        }
        return $this->event;
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
                        $moduleDescription['pollModuleOptions']['pollProposalAddForm'] = $this->pollProposalManager->createPollProposalAddForm($module->getPollModule(), $userModuleInvitation);
                        $moduleDescription['pollModuleOptions']['pollProposalListAddForm'] = $this->pollProposalManager->createPollProposalListAddForm($module->getPollModule(), $userModuleInvitation);
                    }
                    $modules[$module->getId()] = $moduleDescription;
                }
            }
        }
        return $modules;
    }
}