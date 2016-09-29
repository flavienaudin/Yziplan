<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/09/2016
 * Time: 11:44
 */

namespace AppBundle\Utils\enum;

use AppBundle\Utils\enum\AbstractEnumType;

class EventInvitationAnswer extends AbstractEnumType
{
    const DONT_KNOW = "eventInvitation.answer.dont_know";
    const YES = "eventInvitation.answer.yes";
    const NO = "eventInvitation.answer.no";
    const INTERESTED = "eventInvitation.answer.interested";
    const NOT_INTERESTED = "eventInvitation.answer.not_interested";

    protected $name = 'enum_eventinvitation_answer';
    protected $values = array(self::DONT_KNOW, self::YES, self::NO, self::INTERESTED, self::NOT_INTERESTED);
}