<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/12/2016
 * Time: 09:56
 */

namespace AppBundle\EventListener\Comment;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Event\Event;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Mailer\AtTwigSiwftMailer;
use FOS\CommentBundle\Event\CommentEvent;
use FOS\CommentBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiscussionEventSubscriber implements EventSubscriberInterface
{
    /** @var  TokenStorageInterface */
    private $tokenStorage;
    /** @var EventManager $eventManager */
    private $eventManager;
    /** @var EventInvitationManager $eventInvitationManager */
    private $eventInvitationManager;
    /** @var AtTwigSiwftMailer $mailer */
    private $mailer;

    /**
     * CommentEventSubscriber constructor.
     * @param EventManager $eventManager
     * @param EventInvitationManager $eventInvitationManager
     * @param AtTwigSiwftMailer $mailer
     */
    public function __construct(TokenStorageInterface $tokenStorage, EventManager $eventManager, EventInvitationManager $eventInvitationManager, AtTwigSiwftMailer $mailer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->eventManager = $eventManager;
        $this->eventInvitationManager = $eventInvitationManager;
        $this->mailer = $mailer;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::COMMENT_PRE_PERSIST => 'onCommentPrePresist'
        );
    }

    public function onCommentPrePresist(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        if ($comment instanceof Comment) {
            $threadEvent = $this->eventManager->retrieveEvent($comment->getThread()->getId());
            if ($threadEvent instanceof Event) {
                $user = ($this->tokenStorage->getToken() != null && $this->tokenStorage->getToken()->getUser() instanceof AccountUser ? $this->tokenStorage->getToken()->getUser() : null);
                $userEventInvitation = $this->eventInvitationManager->retrieveUserEventInvitation($threadEvent, false, false, $user);
                $comment->setAuthor($userEventInvitation);
            }

        }
    }


}