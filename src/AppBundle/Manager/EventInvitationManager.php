<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/07/2016
 * Time: 18:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Form\Event\EventInvitationAnswerType;
use AppBundle\Form\Event\EventInvitationType;
use AppBundle\Mailer\AppTwigSiwftMailer;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Utils\enum\AppUserStatus;
use AppBundle\Utils\enum\EventInvitationAnswer;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class EventInvitationManager
{
    const TOKEN_SESSION_KEY = "user/eventInvitation/token";

    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var SessionInterface */
    private $session;

    /** @var ApplicationUserManager */
    private $applicationUserManager;

    /** @var  ModuleInvitationManager */
    private $moduleInvitationManager;

    /** @var AppTwigSiwftMailer */
    private $appTwigSiwftMailer;

    /** @var EventInvitation L'invitation à l'événement en cours de traitement */
    private $eventInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory, GenerateursToken $generateurToken, SessionInterface $session, ApplicationUserManager $applicationUserManager, ModuleInvitationManager $moduleInvitationManager, AppTwigSiwftMailer $appTwigSiwftMailer)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->session = $session;
        $this->applicationUserManager = $applicationUserManager;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->appTwigSiwftMailer = $appTwigSiwftMailer;
    }

    /**
     * @return EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }

    /**
     * @param EventInvitation $eventInvitation
     * @return EventInvitationManager
     */
    public function setEventInvitation($eventInvitation)
    {
        $this->eventInvitation = $eventInvitation;
        return $this;
    }

    /**
     * Défini l'EventInvitation en fonction de l'événement fourni et de l'utilisateur (connecté ou non).
     * L'EventInivitation peut être initialisée si les autorisations le permettent.
     *
     * @param $event Event L'événement concerné
     * @param $saveInSession boolean If the session is impacted by the search of the User EventInvitation
     * @param $initializeIfNotExists boolean If true and the EventInvitation doesn't already exist, a new one is initialised
     * @param $user AccountUser|null L'utilisateur connecté ou non
     * @return EventInvitation|null
     */
    public function retrieveUserEventInvitation(Event $event, $saveInSession, $initializeIfNotExists, AccountUser $user = null)
    {
        $this->eventInvitation = null;
        $eventInvitationRepo = $this->entityManager->getRepository(EventInvitation::class);
        if ($user instanceof AccountUser && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
            $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'applicationUser' => $user->getApplicationUser()));
        }
        if ($this->eventInvitation != null) {
            if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                $this->eventInvitation = null;
            }
        } else {
            // Un utilisateur connecté ne peut récupérer une invitation en session sinon il y a risque de récupération d'une invitation qui ne lui appartient pas
            // De plus, un EventInvitation devrait déjà être créé pour lui s'il a déjà tenté de participer à l'événement (ou alors il n'était pas connecté , auquel cas
            // une nouvelle invitation sera créée.
            if ($this->session->has(self::TOKEN_SESSION_KEY)) {
                $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'token' => $this->session->get(self::TOKEN_SESSION_KEY)));
            }
            if ($this->eventInvitation != null) {
                if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                    $this->eventInvitation = null;
                } else {
                    if ($user instanceof AccountUser && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
                        // On vérifie s'il est possible de rattacher l'invitation à l'utilisateur connecté
                        if ($this->eventInvitation->getApplicationUser() == null) {
                            $this->eventInvitation->setApplicationUser($user->getApplicationUser());
                            $this->persistEventInvitation();
                        } elseif ($this->eventInvitation->getApplicationUser()->getAccountUser() == null) {
                            // ApplicationUser de l'EventInvitation ne doit pas avoir d'AppUserEmail ni d'autre information (ni AppUserXxx, ni Contact, ni ContactGroup, ...)
                            // Sinon l'utilisateur aurait déjà récupéré les EventInvitation lors de son inscription
                            $this->applicationUserManager->mergeApplicationUsers($user->getApplicationUser(), $this->eventInvitation->getApplicationUser());
                            $this->eventInvitation->setApplicationUser($user->getApplicationUser());
                            $this->persistEventInvitation();
                        }
                    }
                }
            } else {
                if ($initializeIfNotExists && $this->authorizationChecker->isGranted(EventInvitationVoter::CREATE, $event)) {
                    $this->eventInvitation = $this->initializeEventInvitation($event, ($user instanceof AccountUser ? $user->getApplicationUser() : null));
                    if (!($user instanceof AccountUser)) {
                        // Invitation créée à partir du lien public de l'événement et avec un invité non connecté
                        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_VALIDATION);
                    }
                    $this->persistEventInvitation();
                } else {
                    $this->eventInvitation = null;
                }
            }
        }
        if ($saveInSession) {
            if ($this->eventInvitation != null) {
                $this->session->set(self::TOKEN_SESSION_KEY, $this->eventInvitation->getToken());
            } else {
                $this->session->remove(self::TOKEN_SESSION_KEY);
            }
        }
        return $this->eventInvitation;
    }

    /**
     * Initialise une EventInvitation pour un événement et un ApplicationUser (connecté ou non).
     * L'EventInvitation n'est pas persistée en base de données.
     * @param Event $event
     * @param ApplicationUser|null $applicationUser
     * @return EventInvitation
     */
    public function initializeEventInvitation(Event $event, ApplicationUser $applicationUser = null)
    {
        $this->eventInvitation = new EventInvitation();
        $this->eventInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
        if ($applicationUser != null) {
            $this->eventInvitation->setApplicationUser($applicationUser);
            if (!empty($this->eventInvitation->getDisplayableName())) {
                $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
            }
        }
        $event->addEventInvitation($this->eventInvitation);

        /** @var Module $module */
        foreach ($event->getModules() as $module) {
            // TODO check module authorization (every guests of the event, on ModuleInvitationOnly,...)
            $this->moduleInvitationManager->initializeModuleInvitation($module, $this->eventInvitation, true);
        }

        return $this->eventInvitation;
    }

    /**
     * Persist the EventInvitation into the database and save into the session the Token
     * @return bool true if the eventInvitaiton has been persisted false otherwise
     */
    public function persistEventInvitation()
    {
        if ($this->eventInvitation != null) {
            $this->entityManager->persist($this->eventInvitation);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    /**
     * Retrieve an EventInvitation by the email of the guest and the concerned event
     * @param Event $event The event concerned
     * @param string $email The email of the guest to search the EventInvitation for
     * @return EventInvitation
     */
    public function getGuestEventInvitation(Event $event, $email)
    {
        $this->eventInvitation = null;
        /** @var AppUserEmail $existingAppUserEmail */
        $existingAppUserEmail = $this->applicationUserManager->findAppUserEmailByEmail($email);
        if ($existingAppUserEmail != null) {
            $applicationUser = $existingAppUserEmail->getApplicationUser();
            $this->eventInvitation = $this->entityManager->getRepository(EventInvitation::class)->findOneBy(array('event' => $event, 'applicationUser' => $applicationUser));
        } else {
            $applicationUser = $this->applicationUserManager->createApplicationUserFromEmail($email);
        }
        if ($this->eventInvitation == null) {
            $this->initializeEventInvitation($event, $applicationUser);
        }

        return $this->eventInvitation;
    }

    /**
     * Create and send invitations using emails
     * @param Event $event
     * @param $emailsData
     */
    public function sendInvitations(Event $event, $emailsData, &$failedRecipients = null)
    {
        $failedRecipients = (array)$failedRecipients;
        foreach ($emailsData as $email) {
            $eventInvitation = $this->getGuestEventInvitation($event, $email);
            if ($this->appTwigSiwftMailer->sendEventInvitationEmail($eventInvitation)) {
                $this->persistEventInvitation();
            } else {
                $failedRecipients[] = $email;
            }
        }
    }

    /**
     * Create an EventInvitation and set it as creator of the event
     * @param Event $event
     * @param ApplicationUser|null $applicationUser
     * @return EventInvitation|null If null, the creator is already set
     */
    public function createCreatorEventInvitation(Event $event, ApplicationUser $applicationUser = null)
    {
        $this->initializeEventInvitation($event, $applicationUser);
        $this->eventInvitation->setCreator(true);
        $this->eventInvitation->setAnswer(EventInvitationAnswer::YES);
        return $this->eventInvitation;
    }

    /**
     * Set EventInvitation.status == EventInvitationStatus::CANCELLED
     *
     * @param EventInvitation|null $eventInvitation If null, $this->eventInvitation is used;
     * @return bool true if the update is successful
     */
    public function cancelEventInvitation(EventInvitation $eventInvitation = null)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            $this->eventInvitation->setStatus(EventInvitationStatus::CANCELLED);

            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->eventInvitation->getModuleInvitations() as $moduleInvitation) {
                $moduleInvitation->setStatus(ModuleInvitationStatus::CANCELLED);
            }

            return $this->persistEventInvitation();
        }
        return false;
    }

    /**
     * Génère le formulaire d'édition des informations principales d'une EventInvitation (Réponse à un événement)
     *
     * @param EventInvitation|null $eventInvitation Les données du formulaire
     * @return FormInterface
     */
    public function createEventInvitationForm(EventInvitation $eventInvitation = null)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        return $this->formFactory->create(EventInvitationType::class, $this->eventInvitation);
    }

    /**
     * Traite la soumission du formulaire d'EventInvitationType (Réponse à un événement) en créant un ApplicationUser et User si besoin
     * @param Form $evtInvitForm
     * @return EventInvitation
     */
    public function treatEventInvitationFormSubmission(Form $evtInvitForm)
    {
        $this->eventInvitation = $evtInvitForm->getData();
        if (empty($this->eventInvitation->getDisplayableName()) && $this->eventInvitation->getStatus() == EventInvitationStatus::VALID) {
            // Si le nom est vide => l'invitation revient en attente de réponse
            $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);

            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->eventInvitation->getModuleInvitations() as $moduleInvitation) {
                if ($moduleInvitation->getStatus() == ModuleInvitationStatus::VALID) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::AWAITING_ANSWER);
                }
            }
        } elseif (!empty($this->eventInvitation->getDisplayableName()) &&
            ($this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
        ) {
            // Si le nom n'est pas vide => l'invitation devient valide
            $this->eventInvitation->setStatus(EventInvitationStatus::VALID);

            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->eventInvitation->getModuleInvitations() as $moduleInvitation) {
                if ($moduleInvitation->getStatus() == ModuleInvitationStatus::AWAITING_ANSWER) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::VALID);
                }
            }
        }

        $guestEmailForm = $evtInvitForm->get("email");
        if ($this->eventInvitation->getApplicationUser() == null) {
            $guestEmailData = $guestEmailForm->getData();
            if (!empty($guestEmailData)) {
                /** @var AppUserEmail $appUserEmail */
                $appUserEmail = $this->applicationUserManager->findAppUserEmailByEmail($guestEmailData);
                if ($appUserEmail != null) {
                    $applicationUser = $appUserEmail->getApplicationUser();
                } else {
                    $applicationUser = $this->applicationUserManager->createApplicationUserFromEmail($guestEmailData);
                }
                // If an AppUserEmail exists, it can't be associated to AccountUser due to EmailNotBelongToAccountUser constraint
                $applicationUser->addEventInvitation($this->eventInvitation);
                $this->appTwigSiwftMailer->sendRecapEventInvitationEmail($this->eventInvitation);
            }
        }
        $this->persistEventInvitation();

        return $this->eventInvitation;
    }

    /**
     * Génère le formulaire pour répondre à une EventInvitation (Réponse à un événement)
     *
     * @param EventInvitation|null $eventInvitation Les données du formulaire
     * @return FormInterface
     */
    public function createEventInvitationAnswerForm(EventInvitation $eventInvitation = null)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        return $this->formFactory->create(EventInvitationAnswerType::class, $this->eventInvitation);
    }

    /**
     * Traite la soumission du formulaire d'EventInvitationAnswerType (Réponse à un événement)
     * @param Form $evtInvitAnswerForm
     * @return EventInvitation
     */
    public function treatEventInvitationAnswerFormSubmission(Form $evtInvitAnswerForm)
    {
        $this->eventInvitation = $evtInvitAnswerForm->getData();
        $this->persistEventInvitation();

        return $this->eventInvitation;
    }
}