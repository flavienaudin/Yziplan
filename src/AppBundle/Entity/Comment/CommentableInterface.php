<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/12/2016
 * Time: 15:41
 */

namespace AppBundle\Entity\Comment;

use FOS\CommentBundle\Model\ThreadInterface;

/**
 * Interface CommentableInterface
 * @package AppBundle\Entity\Comment
 */
interface CommentableInterface
{

    /**
     * Set the Thread associated to the entity (null possible)
     *
     * @param ThreadInterface $thread
     * @return mixed
     */
    public function setCommentThread($thread);

    /**
     * @return mixed
     */
    public function getThreadId();
}