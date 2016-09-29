<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/07/2016
 * Time: 18:06
 */

namespace AppBundle\Utils\enum;

class EventInvitationStatus extends AbstractEnumType
{
    /* Values */
    const AWAITING_VALIDATION = "status.awaiting_validation";
    const VALID = "status.valid";
    const CANCELLED = "status.cancelled";

    protected $name = 'enum_eventinvitation_status';
    protected $values = array(self::AWAITING_VALIDATION, self::VALID, self::CANCELLED);

}