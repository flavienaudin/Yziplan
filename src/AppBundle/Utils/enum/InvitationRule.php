<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/05/2017
 * Time: 10:10
 */

namespace AppBundle\Utils\enum;


class InvitationRule extends AbstractEnumType
{
    /* Values */
    const EVERYONE = "modulerule.everyone";
    const EVERYONE_EXCEPT = "modulerule.everyone_except";
    const NONE_EXCEPT= "modulerule.none_except";

    protected $name = 'enum_invitation_rule';
    protected $values = array(self::EVERYONE, self::EVERYONE_EXCEPT, self::NONE_EXCEPT);
}