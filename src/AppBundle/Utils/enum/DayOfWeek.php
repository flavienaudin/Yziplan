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
    const MONDAY =      "day_of_week.1_monday";
    const TUESDAY =     "day_of_week.2_tuesday";
    const WEDNESDAY =   "day_of_week.3_wednesday";
    const THURSDAY =    "day_of_week.4_thursday";
    const FRIDAY =      "day_of_week.5_friday";
    const SATURDAY =    "day_of_week.6_saturday";
    const SUNDAY =      "day_of_week.7_sunday";

    protected $name = 'enum_dayofweek';
    protected $values = array(self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY, self::SUNDAY);
}