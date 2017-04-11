<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 10/04/2017
 * Time: 16:08
 */

namespace AppBundle\Utils\enum;


class NotificationFrequencyEnum extends AbstractEnumType
{
    /* Values */
    const NEVER = 'notification_frequency.never';
    const EACH_NOTIFICATION = 'notification_frequency.each_notification';
    const DAILY = 'notification_frequency.daily';
    const WEEKLY = 'notification_frequency.weekly';

    protected $name = 'enum_notification_frequency';
    protected $values = array(self::NEVER, self::EACH_NOTIFICATION, self::DAILY, self::WEEKLY);
}