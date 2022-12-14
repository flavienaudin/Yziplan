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
use AppBundle\Entity\Notifications\EventInvitationPreferences;
use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Form\Event\EventInvitationAnswerType;
use AppBundle\Form\Event\EventInvitationType;
use AppBundle\Form\Notifications\EventInvitationNotificationPreferencesType;
use AppBundle\Mailer\AppMailer;
use AppBundle\Security\EventInvitationVoter;
use AppBundle\Utils\enum\EventInvitationAnswer;
use AppBundle\Utils\enum\EventInvitationStatus;
use AppBundle\Utils\enum\EventStatus;
use AppBundle\Utils\enum\InvitationRule;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use AppBundle\Utils\enum\ModuleStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
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

    /** @var AppMailer */
    private $appMailer;

    /** @var EventInvitation L'invitation ?? l'??v??nement en cours de traitement */
    private $eventInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory, GenerateursToken $generateurToken, SessionInterface $session, ApplicationUserManager $applicationUserManager, ModuleInvitationManager $moduleInvitationManager, AppMailer $appMailer)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->session = $session;
        $this->applicationUserManager = $applicationUserManager;
        $this->moduleInvitationManager = $moduleInvitationManager;
        $this->appMailer = $appMailer;
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
     * D??fini l'EventInvitation en fonction de l'??v??nement fourni et de l'utilisateur (connect?? ou non).
     * L'EventInivitation peut ??tre initialis??e si les autorisations le permettent.
     *
     * @param $event Event L'??v??nement concern??
     * @param $saveInSession boolean If the session is impacted by the search of the User EventInvitation
     * @param $initializeIfNotExists boolean If true and the EventInvitation doesn't already exist, a new one is initialised
     * @param $user AccountUser|null L'utilisateur connect?? ou non
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
            // TODO : confirmer : si on rajouter la condition : $user == null Alors :
            // Un utilisateur connect?? ne pourra pas r??cup??rer une invitation en session sinon il y a risque de r??cup??ration d'une invitation qui ne lui appartient pas
            // De plus, un EventInvitation devrait d??j?? ??tre cr???? pour lui s'il a d??j?? tent?? de participer ?? l'??v??nement (ou alors il n'??tait pas connect?? , auquel
            // cas une nouvelle invitation sera cr????e.
            // Sauf : que quand un organisateur se connecte/inscris, il ne peut retrouver le contr??le de l'organisation avec une nouvelle invitation
            if ($this->session->has(self::TOKEN_SESSION_KEY . '/' . $event->getToken())) {
                $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'token' => $this->session->get(self::TOKEN_SESSION_KEY . '/' . $event->getToken())));
            }
            if ($this->eventInvitation != null) {
                if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                    $this->eventInvitation = null;
                } else {
                    if ($user instanceof AccountUser && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
                        // On v??rifie s'il est possible de rattacher l'invitation ?? l'utilisateur connect??
                        if ($this->eventInvitation->getApplicationUser() == null) {
                            $this->eventInvitation->setApplicationUser($user->getApplicationUser());
                            if ($this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                                $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
                            }
                            $this->persistEventInvitation();
                        } elseif ($this->eventInvitation->getApplicationUser()->getAccountUser() == null) {
                            // ApplicationUser de l'EventInvitation ne doit pas avoir d'AppUserEmail ni d'autre information (ni AppUserXxx, ni Contact, ni ContactGroup, ...)
                            // Sinon l'utilisateur aurait d??j?? r??cup??r?? les EventInvitation lors de son inscription
                            $this->applicationUserManager->mergeApplicationUsers($user->getApplicationUser(), $this->eventInvitation->getApplicationUser());
                            $this->eventInvitation->setApplicationUser($user->getApplicationUser());
                            if ($this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
                                $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
                            }
                            $this->persistEventInvitation();
                        }
                    }
                }
            } else {
                if ($initializeIfNotExists && $this->authorizationChecker->isGranted(EventInvitationVoter::CREATE, $event)) {
                    $this->eventInvitation = $this->initializeEventInvitation($event, ($user instanceof AccountUser ? $user->getApplicationUser() : null));
                    $this->initializeModuleInvitationStatus();
                    $this->persistEventInvitation();
                } else {
                    $this->eventInvitation = null;
                }
            }
        }
        if ($saveInSession) {
            if ($this->eventInvitation != null) {
                $this->session->set(self::TOKEN_SESSION_KEY . '/' . $event->getToken(), $this->eventInvitation->getToken());
            } else {
                $this->session->remove(self::TOKEN_SESSION_KEY);
            }
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
     * Initialise une EventInvitation pour un ??v??nement et un ApplicationUser (connect?? ou non).
     * L'EventInvitation n'est pas persist??e en base de donn??es
     * @param Event $event
     * @param ApplicationUser|null $applicationUser
     * @return EventInvitation
     */
    public function initializeEventInvitation(Event $event, ApplicationUser $applicationUser = null)
    {
        $this->eventInvitation = new EventInvitation();
        $this->eventInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_VALIDATION);
        if ($applicationUser != null) {
            // L'invitation est attach?? un ApplicationUser => c'est une invitation directe (ie par email) => status = AWAITING_ANSWER
            $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
            $this->eventInvitation->setApplicationUser($applicationUser);
            if (!empty($this->eventInvitation->getDisplayableName(false))) {
                // Le nom de l'invit?? est d??j?? renseign?? (ie attachement ?? un compte utilisateur) => status = VALID
                $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
            }
        }
        $event->addEventInvitation($this->eventInvitation);

        /** @var Module $module */
        foreach ($event->getModules() as $module) {
            $this->moduleInvitationManager->initializeModuleInvitation($module, $this->eventInvitation, true);
        }
        return $this->eventInvitation;
    }

    /**
     * Initialise le statut des ModuleInvitaiton de l'EventInvitation selon la r??gle d'invitation du module et de l'??ventuel pr??c??dent statut du ModuleInvitation
     * @param EventInvitation|null $eventInvitation
     */
    public function initializeModuleInvitationStatus(EventInvitation $eventInvitation = null)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            /** @var ModuleInvitation $moduleInvitation */
            foreach ($this->eventInvitation->getModuleInvitations() as $moduleInvitation) {
                $module = $moduleInvitation->getModule();
                if ($moduleInvitation->isOrganizer()) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                } else {
                    if ($module->getStatus() == ModuleStatus::IN_CREATION) {
                        $moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
                    } else {
                        if ($module->getInvitationRule() == InvitationRule::EVERYONE) {
                            $moduleInvitation->setStatus(ModuleInvitationStatus::INVITED);
                        } elseif ($module->getInvitationRule() == InvitationRule::NONE_EXCEPT) {
                            $moduleInvitation->setStatus(ModuleInvitationStatus::EXCLUDED);
                        }
                    }
                }
            }
        }
    }

    /**
     * Update the EventInvitation lastVisitAt and persist it
     * @param EventInvitation|null $eventInvitation
     * @return bool
     */
    public function updateLastVisit(EventInvitation $eventInvitation = null)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            $this->eventInvitation->setLastVisitAt(new \DateTime());
            return $this->persistEventInvitation();
        }
        return false;
    }

    /**
     * Create and send invitations using emails
     * @param Event $event
     * @param array $emailsData
     * @param string $message
     * @return array with results by success/failed/notFound
     */
    public function createInvitations(Event $event, $emailsData, $message = null)
    {
        $results = array(
            'success' => array(),
            'failed' => array(),
            'creationError' => array()
        );
        foreach ($emailsData as $email) {
            $this->eventInvitation = $this->getGuestEventInvitation($event, $email);
            if ($this->eventInvitation != null) {
                if ($this->eventInvitation->getStatus() == EventInvitationStatus::CANCELLED) {
                    // L'invitation avait ??t?? pr??c??dement annul??e
                    if (!empty($this->eventInvitation->getDisplayableName(false))) {
                        // Envoi d'une invitation avec un nom d??j?? renseign?? => VALID
                        $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
                    } else {
                        // Envoi d'une invitation => AWAITNG_ANSWER
                        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
                    }

                    // V??rification et cr??ation des ModuleInvitation qui n'ont pas ??t?? cr????es pendant que l'invitation ??tait annul??e
                    /** @var Module $module */
                    foreach ($event->getModules() as $module) {
                        if ($module->getStatus() != ModuleStatus::DELETED) {
                            $moduleInvitation = $this->eventInvitation->getModuleInvitationForModule($module);
                            if ($moduleInvitation == null) {
                                $this->moduleInvitationManager->initializeModuleInvitation($module, $this->eventInvitation, true);
                            }
                        }
                    }
                }

                // Mise ?? jour du statut des ModuleInvitations selon le Module.invitationRule
                $this->initializeModuleInvitationStatus();

                try {
                    if ($this->persistEventInvitation()) {
                        if ($this->appMailer->sendEventInvitationEmail($this->eventInvitation, $message)) {
                            $this->eventInvitation->setInvitationEmailSentAt(new \DateTime());
                            $this->persistEventInvitation();
                            $results['success'][$email] = $this->eventInvitation;
                        } else {
                            $results['failed'][$email] = $this->eventInvitation;
                        }
                    } else {
                        $results['creationError'][$email] = $this->eventInvitation;
                    }
                } catch (DBALException $e) {
                    // TODO Si c'est un probl??me de token d??j?? utilis?? (eventInvitation ou ModuleInvitation) => en mettre de nouveau et retenter le persist()
                    $results['creationError'][$email] = $this->eventInvitation;
                }
            } else {
                // error while creating the invitation
                $results['creationError'][] = $email;
            }
        }
        return $results;
    }

    /**
     * Retrieve an EventInvitation by the email of the guest and the concerned event. Initialize a new one if none is found (not persisted)
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
     * @param Collection $eventInvitations
     */
    public function sendInvitations($eventInvitations, &$failedRecipients = array())
    {
        /** @var EventInvitation $eventInvitation */
        foreach ($eventInvitations as $eventInvitation) {
            $this->eventInvitation = $eventInvitation;
            if ($this->appMailer->sendEventInvitationEmail($this->eventInvitation)) {
                $this->eventInvitation->setInvitationEmailSentAt(new \DateTime());
            } else {
                $failedRecipients[] = $this->eventInvitation->getDisplayableEmail();
            }
            $this->persistEventInvitation();
        }
    }

    /**
     * @param Collection $eventInvitations
     */
    public function sendMessage($eventInvitations, $message = null, &$failedRecipients = array())
    {
        /** @var EventInvitation $eventInvitation */
        foreach ($eventInvitations as $eventInvitation) {
            $this->eventInvitation = $eventInvitation;
            if (!$this->appMailer->sendMessageEmail($this->eventInvitation, $message)) {
                $failedRecipients[] = $this->eventInvitation;
            }
        }
    }

    /**
     * Cr???? l'EventInvitation et la d??signe l'invit?? en tant que cr??ateur de l'??v??nement
     * NB : L'EventInvitation et les ModuleInvitation ne sont pas persist??es
     * @param Event $event
     * @param ApplicationUser|null $applicationUser
     * @return EventInvitation|null If null, the creator is already set
     */
    public function createCreatorEventInvitation(Event $event, ApplicationUser $applicationUser = null)
    {
        $this->initializeEventInvitation($event, $applicationUser);
        $this->eventInvitation->setCreator(true);
        $this->eventInvitation->setAnswer(EventInvitationAnswer::YES);
        if ($applicationUser != null && $applicationUser->getAccountUser() instanceof AccountUser) {
            $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
        } else {
            $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_VALIDATION);
        }
        return $this->eventInvitation;
    }

    /**
     * Set EventInvitation.answer == $answerValue
     *
     * @param EventInvitation|null $eventInvitation If null, $this->eventInvitation is used
     * @param $answerValue string La nouvelle valeur de la r??ponse
     * @return bool true if the update is successful
     */
    public function modifyAnswerEventInvitation(EventInvitation $eventInvitation = null, $answerValue)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            $this->eventInvitation->setAnswer($answerValue);
            return $this->persistEventInvitation();
        }
        return false;
    }

    /**
     * Set EventInvitation.administrator = $value
     *
     * @param EventInvitation|null $eventInvitation If null, $this->eventInvitation is used
     * @param $value boolean Si l'EventInvitation devient ou non un administrateur
     * @return bool true if the update is successful
     */
    public function designateGuestAsAdministrator(EventInvitation $eventInvitation = null, $value)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            $this->eventInvitation->setAdministrator($value);
            return $this->persistEventInvitation();
        }
        return false;
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
                if ($moduleInvitation->getStatus() == ModuleInvitationStatus::INVITED) {
                    $moduleInvitation->setStatus(ModuleInvitationStatus::NOT_INVITED);
                }
                // excluded reste excluded
            }

            return $this->persistEventInvitation();
        }
        return false;
    }


    /**
     * Change EventInvitation.archive value
     *
     * @param EventInvitation|null $eventInvitation If null, $this->eventInvitation is used
     * @param boolean $archived The new value of archived attribute
     * @return bool true if the update is successful
     */
    public function archiveEventInvitation(EventInvitation $eventInvitation = null, $archived)
    {
        if ($eventInvitation != null) {
            $this->eventInvitation = $eventInvitation;
        }
        if ($this->eventInvitation != null) {
            $this->eventInvitation->setArchived($archived);
            return $this->persistEventInvitation();
        }
        return false;
    }

    /**
     * G??n??re le formulaire d'??dition des informations principales d'une EventInvitation (R??ponse ?? un ??v??nement)
     *
     * @param EventInvitation|null $eventInvitation Les donn??es du formulaire
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
     * Traite la soumission du formulaire d'EventInvitationType (R??ponse ?? un ??v??nement) en cr??ant un ApplicationUser et User si besoin
     * @param FormInterface $evtInvitForm
     * @return EventInvitation
     */
    public function treatEventInvitationFormSubmission(FormInterface $evtInvitForm)
    {
        $this->eventInvitation = $evtInvitForm->getData();
        if (empty($this->eventInvitation->getDisplayableName(false)) && $this->eventInvitation->getStatus() == EventInvitationStatus::VALID) {
            // Si le nom est vide et l'invitation ??tait valide
            if (empty($this->eventInvitation->getDisplayableEmail())) {
                // Si l'email est vide => l'invitation revient en attente de validation
                $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_VALIDATION);
            } else {
                // Si l'email est renseign?? => l'invitation revient en attente de r??ponse
                $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
            }
        } elseif (!empty($this->eventInvitation->getDisplayableName(false)) &&
            ($this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION || $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER)
        ) {
            // Si le nom n'est pas vide => l'invitation devient valide
            $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
            if ($this->eventInvitation->getEvent()->getStatus() == EventStatus::IN_CREATION && $this->eventInvitation->isOrganizer()) {
                $this->eventInvitation->getEvent()->setStatus(EventStatus::IN_ORGANIZATION);
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
                $this->appMailer->sendRecapEventInvitationEmail($this->eventInvitation);
            }
        }
        $this->persistEventInvitation();

        return $this->eventInvitation;
    }

    /**
     * G??n??re le formulaire pour r??pondre ?? une EventInvitation (R??ponse ?? un ??v??nement)
     *
     * @param EventInvitation|null $eventInvitation Les donn??es du formulaire
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
     * Traite la soumission du formulaire d'EventInvitationAnswerType (R??ponse ?? un ??v??nement)
     * @param FormInterface $evtInvitAnswerForm
     * @return EventInvitation
     */
    public function treatEventInvitationAnswerFormSubmission(FormInterface $evtInvitAnswerForm)
    {
        $this->eventInvitation = $evtInvitAnswerForm->getData();
        $this->persistEventInvitation();

        return $this->eventInvitation;
    }

    /**
     * G??n??re le formulaire de gestion des pr??f??renes de notification par email d'une invitation
     * @return FormInterface
     */
    public function createNotificationPreferencesForm()
    {
        if ($this->eventInvitation->getEventInvitationPreferences() === null) {
            $this->eventInvitation->setEventInvitationPreferences(new EventInvitationPreferences());
        }
        return $this->formFactory->create(EventInvitationNotificationPreferencesType::class, $this->eventInvitation->getEventInvitationPreferences());
    }


    /**
     * Traite la soumission du formulaire de gestion des pr??f??renes de notification par email d'une invitation
     * @param FormInterface $form
     * @return EventInvitation
     */
    public function treatNotificationPreferencesForm(FormInterface $form)
    {
        if ($this->eventInvitation != null) {
            $this->persistEventInvitation();
        }
        return $this->eventInvitation;
    }
}