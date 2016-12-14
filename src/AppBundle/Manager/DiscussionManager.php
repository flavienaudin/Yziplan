<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/12/2016
 * Time: 15:11
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Comment\Thread;
use AppBundle\Entity\Event\Event;
use FOS\CommentBundle\Entity\CommentManager;
use FOS\CommentBundle\Entity\ThreadManager;

class DiscussionManager
{
    /** @var EventManager $eventManager */
    private $eventManager;
    /** @var ThreadManager $threadManager */
    private $threadManager;
    /** @var CommentManager $commentManager */
    private $commentManager;
    /** @var Thread */
    private $thread;
    /** @var Comment[] */
    private $comments;


    /**
     * EventCommentManager constructor.
     * @param EventManager $eventManager
     * @param CommentManager $commentManager
     * @param ThreadManager $threadManager
     */
    public function __construct(EventManager $eventManager, ThreadManager $threadManager, CommentManager $commentManager)
    {
        $this->eventManager = $eventManager;
        $this->commentManager = $commentManager;
        $this->threadManager = $threadManager;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param Thread $thread
     * @return DiscussionManager
     */
    public function setThread($thread)
    {
        $this->thread = $thread;
        return $this;
    }

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     * @return DiscussionManager
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function createEventThread(Event $event, $uri)
    {
        $this->thread = $this->threadManager->createThread();
        $this->thread->setId($event->getToken());
        $this->thread->setPermalink($uri);
        // Add the thread
        $this->threadManager->saveThread($this->thread);

        $event->setCommentThread($this->thread);
        $this->eventManager
            ->setEvent($event)
            ->persistEvent();
        return $this->thread;
    }

    public function getCommentsThread(Thread $thread = null)
    {
        if ($thread != null) {
            $this->thread = $thread;
        }
        if ($this->thread != null) {
            $this->comments = $this->commentManager->findCommentTreeByThread($this->thread);
        } else {
            $this->comments = array();
        }
        return $this->comments;
    }

}