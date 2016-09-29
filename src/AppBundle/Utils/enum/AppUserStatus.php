<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 15:01
 */

namespace AppBundle\Utils\enum;


class AppUserStatus extends AbstractEnumType
{
    /* Values */
    const MAIN = "appuser.status.main";
    const MERGED = "appuser.status.merged";

    protected $name = 'enum_appuser_status';
    protected $values = array(self::MAIN, self::MERGED);
}