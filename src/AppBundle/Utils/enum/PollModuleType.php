<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/11/2016
 * Time: 12:26
 */

namespace AppBundle\Utils\enum;


class PollModuleType extends AbstractEnumType
{
    /* Values */
    const ACTIVITY = "pollmoduletype.activity";
    const WHAT= "pollmoduletype.what";
    const WHEN = "pollmoduletype.when";
    const WHERE = "pollmoduletype.where";
    const WHO_BRINGS_WHAT = "pollmoduletype.whobringswhat";

    protected $name = 'enum_pollmodule_type';
    protected $values = array(self::ACTIVITY, self::WHAT, self::WHEN, self::WHERE, self::WHO_BRINGS_WHAT);
}