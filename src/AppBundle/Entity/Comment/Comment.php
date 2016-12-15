<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/12/2016
 * Time: 10:43
 */

namespace AppBundle\Entity\Comment;


use AppBundle\Entity\Event\EventInvitation;
use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as BaseFosComment;

/**
 * @ORM\Table(name="event_comment")
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Comment extends BaseFosComment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Thread of this comment
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Comment\Thread")
     */
    protected $thread;

    /**
     * Author of the comment
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\EventInvitation")
     * @var EventInvitation
     */
    protected $author;

    public function setAuthor(EventInvitation $author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }
    public function getAuthorName()
    {
        $name = null;
        if ($this->getAuthor() != null) {
            $name = $this->getAuthor()->getDisplayableName();
        }
        if(empty($name)){
            $name = 'Yziplan-Onymous';
        }

        return $name;
    }
}