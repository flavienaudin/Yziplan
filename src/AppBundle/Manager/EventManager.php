<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:39
 */

namespace AppBundle\Manager;


use AppBundle\Entity\AppUser;
use AppBundle\Entity\enum\EventStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvitation;
use AppBundle\Form\EventFormType;
use ATUserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class EventManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FormFactory */
    private $formFactory;

    /** @var GenerateursToken */
    private $generateursToken;

    /** @var Event L'événement en cours de traitement */
    private $event;

    /** @var boolean */
    private $sendRecapEmail;

    public function __construct(EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker, FormFactory $formFactory, GenerateursToken $generateurToken)
    {
        $this->entityManager = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->generateursToken = $generateurToken;
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
     * @return bool true Si un événement est trouvé
     */
    public function retrieveEvent($token)
    {
        if ($token == null) {
            $this->event = new Event();
        } else {
            $eventRep = $this->entityManager->getRepository("AppBundle:Event");
            $this->event = $eventRep->findOneBy(array('token' => $token));
        }
        return ($this->event instanceof Event);
    }


    /**
     * @param $user User|string Utilisateur connecté
     * @return Form Formulaire de création/édition d'un événement
     */
    public function initEventForm($user)
    {
        if ($this->event->getStatus() == null) {
            $this->event->setStatus(EventStatus::IN_ORGANIZATION);
        }
        if ( $this->event->getCreator()==null && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED) && $user instanceof User ) {
            $creatorInvitation = new EventInvitation();
            $creatorInvitation->setAppUser($user->getAppUser());
            $creatorInvitation->setEvent($this->event);
            $this->event->setCreator($creatorInvitation);
            $this->event->addEventInvitation($creatorInvitation);
        }

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

}