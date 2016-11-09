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
    const WHAT= "pollmoduletype.what";
    const WHEN = "pollmoduletype.when";
    //const WHERE = "pollmoduletype.where";

    protected $name = 'enum_pollmodule_type';
    protected $values = array(self::WHAT, self::WHEN);
}