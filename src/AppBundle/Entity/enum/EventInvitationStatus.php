<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/07/2016
 * Time: 18:06
 */

namespace AppBundle\Entity\enum;


class EventInvitationStatus
{
    const AWAITING_VALIDATION="status.awaiting_validation";
    const VALID = "status.valid";
    const CANCELLED = "status.cancelled";
}