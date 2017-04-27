<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/12/2016
 * Time: 15:11
 */

namespace AppBundle\Manager;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Comment\CommentableInterface;
use AppBundle\Entity\Comment\Thread;
use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Entity\Notifications\Notification;
use Doctrine\ORM\EntityManager;
use FOS\CommentBundle\Entity\CommentManager;
use FOS\CommentBundle\Entity\ThreadManager;

class DiscussionManager
{
    /** @var EntityManager $entityManager */
    private $entityManager;
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
     * @param EntityManager $entityManager
     * @param CommentManager $commentManager
     * @param ThreadManager $threadManager
     */
    public function __construct(EntityManager $entityManager, ThreadManager $threadManager, CommentManager $commentManager)
    {
        $this->entityManager = $entityManager;
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

    /**
     * @param CommentableInterface $commentable
     * @param $uri
     * @return Thread|\FOS\CommentBundle\Model\Thread
     */
    public function createCommentableThread(CommentableInterface $commentable)
    {
        $this->thread = $this->threadManager->createThread();
        $this->thread->setId($commentable->getThreadId());
        // Add the thread
        $this->threadManager->saveThread($this->thread);

        $commentable->setCommentThread($this->thread);
        $this->entityManager->persist($commentable);
        $this->entityManager->flush();
        return $this->thread;
    }

    /**
     * Retrieve comments of thread (optionnally given as parameter)
     * @param Thread|null $thread
     * @return Comment[]|array
     */
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

    /**
     * Retourne les notifications à afficher en fonction de l'invitation et des commentaires
     *
     * @param EventInvitation $eventInvitation L'invitation de reférence
     * @param $comments array Les commentaires concernés
     * @return Notification
     */
//    public function getNotification(EventInvitation $eventInvitation, $comments, $subject)
//    {
//        $new_comments_cpt = 0;
//        $first_new_comments_date = new \DateTime();
//        foreach ($comments as $commentAndChildren) {
//            /** @var Comment $comment */
//            $comment = $commentAndChildren['comment'];
//            if ($comment->getCreatedAt() > $eventInvitation->getLastVisitAt()) {
//                $new_comments_cpt++;
//                if ($comment->getCreatedAt() < $first_new_comments_date) {
//                    $first_new_comments_date = $comment->getCreatedAt();
//                }
//            }
//        }
//        if ($new_comments_cpt > 0) {
//            $notif = new Notification();
//            $notif->setDate($first_new_comments_date);
//            $notif->setType(\AppBundle\Utils\enum\NotificationTypeEnum::POST_COMMENT);
//            $notif->setData(array(
//                "new_comments_number" => $new_comments_cpt,
//                "subject" => array(
//                    'token' => $subject->getToken(),
//                    'name' => $subject->getName()
//                )
//            ));
//            return $notif;
//        }
//        return null;
//    }
}