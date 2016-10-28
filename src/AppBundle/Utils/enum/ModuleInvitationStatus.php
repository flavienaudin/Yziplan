<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/07/2016
 * Time: 11:03
 */

namespace AppBundle\Utils\enum;


class ModuleInvitationStatus extends AbstractEnumType
{
    const AWAITING_ANSWER="moduleinvitationstatus.awaiting_answer";
    const VALID = "moduleinvitationstatus.valid";
    const CANCELLED = "moduleinvitationstatus.cancelled";

    protected $name = 'enum_moduleinvitation_status';
    protected $values = array(self::AWAITING_ANSWER, self::VALID, self::CANCELLED);
}