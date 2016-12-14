<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/12/2016
 * Time: 10:45
 */

namespace AppBundle\Entity\Comment;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as BaseFosThread;

/**
 * @ORM\Table(name="event_thread")
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Thread extends BaseFosThread
{
    /**
     * @var string $id
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;
}