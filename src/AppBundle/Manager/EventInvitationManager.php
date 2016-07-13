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
use AppBundle\Security\EventInvitationVoter;
use ATUserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
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

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var SessionInterface */
    private $session;

    /** @var EventInvitation L'événement en cours de traitement */
    private $eventInvitation;


    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, GenerateursToken $generateurToken, SessionInterface $session)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->generateursToken = $generateurToken;
        $this->session = $session;
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

    public function retrieveUserEventInvitation(Event $event, User $user = null)
    {
        $this->eventInvitation = null;
        $eventInvitationRepo = $this->entityManager->getRepository("AppBundle:EventInvitation");
        if ($user instanceof User && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, $user)) {
            $this->eventInvitation = $eventInvitationRepo->findOneBy(array('event' => $event, 'appUser' => $user->getAppUser()));
        }
        if ($this->eventInvitation == null) {
            if ($this->session->has(self::TOKEN_SESSION_KEY)) {
                $this->eventInvitation = $eventInvitationRepo->findOneBy(array('token' => $this->session->get(self::TOKEN_SESSION_KEY)));
            }
            if (!$this->authorizationChecker->isGranted(EventInvitationVoter::EDIT, $this->eventInvitation)) {
                $this->eventInvitation = null;
            }
            if($this->eventInvitation == null && $this->authorizationChecker->isGranted(EventInvitationVoter::CREATE, $user)){
                $this->eventInvitation = $this->initializeEventInvitation($event, ($user instanceof User ? $user : null));
            }
        }

        return $this->eventInvitation;
    }


    public function initializeEventInvitation(Event $event, User $user = null)
    {
        $this->eventInvitation = new EventInvitation();
        $this->eventInvitation->setToken($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        $this->eventInvitation->setTokenEdition($this->generateursToken->random(GenerateursToken::TOKEN_LONGUEUR));
        if ($user != null) {
            $this->eventInvitation->setAppUser($user->getAppUser());
        }
        $event->addEventInvitation($this->eventInvitation);
        $this->eventInvitation->setStatus(EventInvitationStatus::AWAITING_ANSWER);
        return $this->eventInvitation;
    }

}