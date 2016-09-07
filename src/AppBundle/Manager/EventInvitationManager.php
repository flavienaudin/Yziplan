<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/07/2016
 * Time: 18:12
 */

namespace AppBundle\Manager;


use AppBundle\Entity\enum\EventInvitationStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Entity\Module;
use AppBundle\Form\EventInvitationFormType;
use AppBundle\Form\InvitationsFormType;
use AppBundle\Security\EventInvitationVoter;
use ATUserBundle\Entity\User;
use ATUserBundle\Manager\UserManager;
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
    const TOKEN_EDITION_SESSION_KEY = "user/eventInvitation/tokenEdition";

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

    /** @var UserManager */
    private $userManager;

    /** @var  ModuleInvitationManager */
    private $moduleInvitationManager;

    /** @var EventInvitation L'invitation à l'événement en cours de traitement */
    private $eventInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory, GenerateursToken $generateurToken, SessionInterface $session, UserManager $userManager, ModuleInvitationManager $moduleInvitationManager)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->session = $session;
        $this->userManager = $userManager;
        $this->moduleInvitationManager = $moduleInvitationManager;
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
     * @param $user User|null L'utilisateur connecté ou non
     * @return EventInvitation|null
     */
    public function retrieveUserEventInvitation(Event $event, $saveInSession, $initializeIfNotExists, User $user = null)
    {
        $this->eventInvitation = null;
        $eventInvitationRepo = $this->entityManager->getRepository("AppBundle:EventInvitation");
        if ($user instanceof User && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
            $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'appUser' => $user->getAppUser()));
        }
        if ($this->eventInvitation != null) {
            if ($this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
            } else {
                $this->eventInvitation = null;
            }
        } else {
            if ($this->session->has(self::TOKEN_SESSION_KEY)) {
                $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'token' => $this->session->get(self::TOKEN_SESSION_KEY)));
            }
            if ($this->eventInvitation != null) {
                if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                    $this->eventInvitation = null;
                }
            } else {
                if ($initializeIfNotExists && $this->authorizationChecker->isGranted(EventInvitationVoter::CREATE, $event)) {
                    $this->eventInvitation = $this->initializeEventInvitation($event, ($user instanceof User ? $user : null));
                    $this->updateEventInvitation();
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
     * Retrieve an EventInvitation by the email of the guest and the concerned event
     * @param Event $event The event concerned
     * @param string $email The email of the guest to search the EventInvitation for
     * @return EventInvitation
     */
    public function getGuestEventInvitation(Event $event, $email){
        $this->eventInvitation = null;
        $guestUser = $this->userManager->findUserByEmail($email);
        if($guestUser != null){
            $eventInvitationRepo = $this->entityManager->getRepository("AppBundle:EventInvitation");
            $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'appUser' => $guestUser->getAppUser()));
        }else{
            $guestUser = $this->userManager->createUserFromEmail($email);
        }
        if($this->eventInvitation == null) {
             $this->initializeEventInvitation($event, $guestUser);
        }

        return $this->eventInvitation;
    }

    /**
     * Create an EventInvitation and set it as creator of the event
     * @param Event $event
     * @param User|null $user
     * @return EventInvitation|null If null, the creator is already set
     */
    public function createCreatorEventInvitation(Event $event, User $user = null)
    {
        if ($event->getCreator() != null) {
            $this->eventInvitation = null;
        } else {
            $this->initializeEventInvitation($event, $user);
            $event->setCreator($this->eventInvitation);
        }
        return $this->eventInvitation;
    }

    /**
     * Initialise une EventInvitation pour un événement et d'un utilisateur (connecté ou non).
     * L'EventInvitation n'est pas persistée en base de données.
     * @param Event $event
     * @param User|null $user
     * @return EventInvitation
     */
    public function initializeEventInvitation(Event $event, User $user = null)
    {
        $this->eventInvitation = new EventInvitation();
        $this->eventInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->eventInvitation->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_VALIDATION);
        if ($user != null) {
            $this->eventInvitation->setAppUser($user->getAppUser());
            $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
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
            return $this->updateEventInvitation();
        }
        return false;
    }

    /**
     * Persist the EventInvitation into the database and save into the session the Token
     * @return bool true if the eventInvitaiton has been persisted false otherwise
     */
    public function updateEventInvitation()
    {
        if ($this->eventInvitation != null) {
            $this->entityManager->persist($this->eventInvitation);
            $this->entityManager->flush();
            $this->session->set(self::TOKEN_SESSION_KEY, $this->eventInvitation->getToken());
            return true;
        }
        return false;
    }

    /**
     * Génère le formulaire d'édition des informations principales d'une EventInvitation (Réponse à un événement)
     * @return FormInterface
     */
    public function createEventInvitationForm()
    {
        return $this->formFactory->create(EventInvitationFormType::class, $this->eventInvitation);
    }

    /**
     * Traite la soumission du formulaire d'EventInvitationFormType (Réponse à un événement) en créant un AppUser et User si besoin
     * @param Form $evtForm
     * @return EventInvitation|mixed
     */
    public function treatEventInvitationFormSubmission(Form $evtForm)
    {
        $this->eventInvitation = $evtForm->getData();
        if (!empty($this->eventInvitation->getDisplayableName()) && $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_VALIDATION) {
            $this->eventInvitation->setStatus(EventInvitationStatus::VALID);
        }

        $guestEmailForm = $evtForm->get("email");
        if ($this->eventInvitation->getAppUser() == null) {
            $guestEmailData = $guestEmailForm->getData();
            if (!empty($guestEmailData)) {
                $guest = $this->userManager->findUserByEmail($guestEmailData);
                if ($guest == null) {
                    $guest = $this->userManager->createUserFromEmail($guestEmailData);
                }
                $guest->getAppUser()->addEventInvitation($this->eventInvitation);

            }
        }
        $this->updateEventInvitation();

        return $this->eventInvitation;
    }
}