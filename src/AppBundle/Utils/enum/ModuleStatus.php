<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/06/2016
 * Time: 15:15
 */

namespace AppBundle\Utils\enum;

/**
 * Class ModuleStatus Défini les états possibles pour un module (status).
 */
class ModuleStatus extends AbstractEnumType
{
    /* Values */
    const IN_CREATION = "status.in_creation";
    const IN_ORGANIZATION = "status.in_organization";
    const AWAITING_VALIDATION = "status.awaiting_validation";
    const VALIDATED = "status.validated";
    const ARCHIVED = "status.archived";
    const DELETED = "status.deleted";

    protected $name = 'enum_module_status';
    protected $values = array(self::IN_CREATION, self::IN_ORGANIZATION, self::AWAITING_VALIDATION, self::VALIDATED, self::ARCHIVED, self::DELETED);
}