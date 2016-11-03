<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/06/2016
 * Time: 15:15
 */

namespace AppBundle\Utils\enum;


class ModuleStatus extends AbstractEnumType
{
    /* Values */
    const IN_CREATION = "modulestatus.in_creation";
    const IN_ORGANIZATION = "modulestatus.in_organization";
    const AWAITING_VALIDATION = "modulestatus.awaiting_validation";
    const VALIDATED = "modulestatus.validated";
    const ARCHIVED = "modulestatus.archived";
    const DELETED = "modulestatus.deleted";

    protected $name = 'enum_module_status';
    protected $values = array(self::IN_CREATION, self::IN_ORGANIZATION, self::AWAITING_VALIDATION, self::VALIDATED, self::ARCHIVED, self::DELETED);
}