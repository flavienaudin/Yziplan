<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:10
 */

namespace AppBundle\Utils\enum;

/**
 * Class EventStatus Défini les états possibles pour un événément (status).
 * @package AppBundle\Entity\enum
 */
class EventStatus extends AbstractEnumType
{
    /* Values */
    const IN_CREATION = "eventstatus.in_creation";
    const IN_ORGANIZATION = "eventstatus.in_organization";
    const AWAITING_VALIDATION = "eventstatus.awaiting_validation";
    const VALIDATED = "eventstatus.validated";
    const ARCHIVED = "eventstatus.archived";
    const DEPROGRAMMED = "eventstatus.deprogrammed";

    protected $name = 'enum_event_status';
    protected $values = array(self::IN_CREATION, self::IN_ORGANIZATION, self::AWAITING_VALIDATION, self::VALIDATED, self::ARCHIVED, self::DEPROGRAMMED);
}