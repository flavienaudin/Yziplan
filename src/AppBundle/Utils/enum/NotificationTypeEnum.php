<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/03/2017
 * Time: 15:00
 */

namespace AppBundle\Utils\enum;


use AppBundle\Entity\Comment\Comment;
use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Module\PollProposal;

class NotificationTypeEnum
{
    /* Values */
    const COMMENT = Comment::class;
    const MODULE = Module::class;
    const POLL_PROPOSAL = PollProposal::class;

    protected $name = 'enum_notification_type';
    protected $values = array(self::COMMENT, self::MODULE, self::POLL_PROPOSAL);
}