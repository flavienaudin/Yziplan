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

class NotificationTypeEnum extends AbstractEnumType
{
    /* Values */
    const POST_COMMENT = 'notification_type.post_comment';
    const ADD_MODULE = 'notification_type.add_module';
    const ADD_POLL_PROPOSAL = 'notification_type.add_pollproposal';

    protected $name = 'enum_notification_type';
    protected $values = array(self::POST_COMMENT, self::ADD_MODULE, self::ADD_POLL_PROPOSAL);
}
