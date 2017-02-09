<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 15:01
 */

namespace AppBundle\Utils\enum;


class DayOfWeek extends AbstractEnumType
{
    /* Values */
    const MONDAY = "day_of_week.monday";
    const TUESDAY = "day_of_week.tuesday";
    const WEDNESDAY = "day_of_week.wednesday";
    const THURSDAY = "day_of_week.thursday";
    const FRIDAY = "day_of_week.friday";
    const SATURDAY = "day_of_week.saturday";
    const SUNDAY = "day_of_week.sunday";

    protected $name = 'enum_dayofweek';
    protected $values = array(self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY, self::SUNDAY);
}