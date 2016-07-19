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
use AppBundle\Entity\ModuleInvitation;
use AppBundle\Form\EventInvitationFormType;
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

    /** @var EventInvitation L'événement en cours de traitement */
    private $eventInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactoryInterface $formFactory, GenerateursToken $generateurToken, SessionInterface $session, UserManager $userManager)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
        $this->session = $session;
        $this->userManager = $userManager;
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
     * @param Event $event L'événement concerné
     * @param User|null $user L'utilisateur conntcté ou non
     * @return EventInvitation|null
     */
    public function retrieveUserEventInvitation(Event $event, User $user = null)
    {
        $this->eventInvitation = null;
        $eventInvitationRepo = $this->entityManager->getRepository("AppBundle:EventInvitation");
        if ($user instanceof User && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
            $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'appUser' => $user->getAppUser()));
        }
        if ($this->eventInvitation != null) {
            $this->session->set(self::TOKEN_SESSION_KEY, $this->eventInvitation->getToken());
        } else {
            if ($this->session->has(self::TOKEN_SESSION_KEY)) {
                $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event,'token' => $this->session->get(self::TOKEN_SESSION_KEY)));
            }
            if ($this->eventInvitation != null) {
                if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                    $this->eventInvitation = null;
                }
            } else {
                if ($this->authorizationChecker->isGranted(EventInvitationVoter::CREATE, $event)) {
                    $this->eventInvitation = $this->initializeEventInvitation($event, ($user instanceof User ? $user : null));
                } else {
                    $this->eventInvitation = null;
                }
            }
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
        if ($user != null) {
            $this->eventInvitation->setAppUser($user->getAppUser());
        }
        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
        $event->addEventInvitation($this->eventInvitation);

        foreach ($event->getModules() as $module) {
            // TODO check module authorization (every guests of the event, on ModuleInvitationOnly,...)
            $moduleInvitation = new ModuleInvitation();
            $moduleInvitation->setModule($module);
            $this->eventInvitation->addModuleInvitation($moduleInvitation);
        }

        return $this->eventInvitation;
    }

    /**
     * Génère le formulaire d'édition des informations principale d'une EventInvitation
     * @return FormInterface
     */
    public function createEventInvitationForm()
    {
        return $this->formFactory->create(EventInvitationFormType::class, $this->eventInvitation);
    }

    /**
     * Traite la soumission du formulaire d'EventInvitationFormType en créatnt un AppUser et User si besoin
     * @param Form $evtForm
     * @return EventInvitation|mixed
     */
    public function treatEventFormSubmission(Form $evtForm)
    {
        $this->eventInvitation = $evtForm->getData();
        if (!empty($this->eventInvitation->getName()) && $this->eventInvitation->getStatus() == EventInvitationStatus::AWAITING_ANSWER) {
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

        $this->entityManager->persist($this->eventInvitation);
        $this->entityManager->flush();
        $this->session->set(self::TOKEN_SESSION_KEY, $this->eventInvitation->getToken());

        return $this->eventInvitation;
    }
}