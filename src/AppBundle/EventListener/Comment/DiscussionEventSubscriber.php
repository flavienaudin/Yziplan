<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/12/2016
 * Time: 09:56
 */

namespace AppBundle\EventListener\Comment;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Comment\Thread;
use AppBundle\Entity\Event\Event;
use AppBundle\Manager\EventInvitationManager;
use AppBundle\Manager\EventManager;
use AppBundle\Manager\ModuleManager;
use AppBundle\Manager\NotificationManager;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Mailer\AtTwigSiwftMailer;
use FOS\CommentBundle\Event\CommentEvent;
use FOS\CommentBundle\Event\ThreadEvent;
use FOS\CommentBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiscussionEventSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;
    /** @var RequestStack $requestStack */
    private $requestStack;
    /** @var EventManager $eventManager */
    private $eventManager;
    /** @var ModuleManager $moduleManager */
    private $moduleManager;
    /** @var EventInvitationManager $eventInvitationManager */
    private $eventInvitationManager;
    /** @var NotificationManager $notificationManager */
    private $notificationManager;
    /** @var AtTwigSiwftMailer $mailer */
    private $mailer;

    /**
     * DiscussionEventSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack $requestStack
     * @param EventManager $eventManager
     * @param ModuleManager $moduleManager
     * @param EventInvitationManager $eventInvitationManager
     * @param AtTwigSiwftMailer $mailer
     * @param NotificationManager $notificationManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, RequestStack $requestStack, EventManager $eventManager, ModuleManager $moduleManager, EventInvitationManager $eventInvitationManager, AtTwigSiwftMailer $mailer, NotificationManager $notificationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->eventInvitationManager = $eventInvitationManager;
        $this->mailer = $mailer;
        $this->notificationManager = $notificationManager;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::THREAD_CREATE => 'onThreadCreate',
            Events::COMMENT_PRE_PERSIST => 'onCommentPrePresist'
        );
    }

    public function onThreadCreate(ThreadEvent $threadEvent)
    {
        $thread = $threadEvent->getThread();
        if ($thread instanceof Thread) {
            if (empty($thread->getPermalink())) {
                $request = $this->requestStack->getCurrentRequest();
                if ($request != null) {
                    $thread->setPermalink($request->getUri());
                } else {
                    $thread->setPermalink("");
                }
            }
        }
    }

    public function onCommentPrePresist(CommentEvent $commentEvent)
    {
        $comment = $commentEvent->getComment();
        if ($comment instanceof Comment) {
            $threadId = $comment->getThread()->getId();
            $threadEvent = null;
            $sepIdx = strpos($threadId, '_');
            if ($sepIdx === false) {
                $threadEvent = $this->eventManager->retrieveEvent($comment->getThread()->getId());
            } else {
                $threadId = substr($threadId, $sepIdx + 1);
                $threadedModule = $this->moduleManager->retrieveModuleByToken($threadId);
                $threadEvent = $threadedModule->getEvent();
            }
            if ($threadEvent instanceof Event) {
                $user = ($this->tokenStorage->getToken() != null && $this->tokenStorage->getToken()->getUser() instanceof AccountUser ? $this->tokenStorage->getToken()->getUser() : null);
                $userEventInvitation = $this->eventInvitationManager->retrieveUserEventInvitation($threadEvent, false, false, $user);
                $comment->setAuthor($userEventInvitation);
                $this->notificationManager->createNewCommentNotifications($comment, (isset($threadedModule) ? $threadedModule : $threadEvent), $userEventInvitation);
            }
        }
    }


}