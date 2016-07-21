<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/07/2016
 * Time: 11:03
 */

namespace AppBundle\Entity\enum;


class ModuleInvitationStatus
{
    const AWAITING_ANSWER="status.awaiting_answer";
    const VALID = "status.valid";
    const CANCELLED = "status.cancelled";
}