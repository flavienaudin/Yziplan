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
    const EVERYONE = "invitationrule.everyone";
    const NONE_EXCEPT= "invitationrule.none_except";

    protected $name = 'enum_invitation_rule';
    protected $values = array(self::EVERYONE, self::NONE_EXCEPT);
}