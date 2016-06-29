<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/06/2016
 * Time: 15:15
 */

namespace AppBundle\Entity\enum;


/**
 * Class ModuleStatus Défini les états possibles pour un module (status).
 * @package AppBundle\Entity\enum
 */
class ModuleStatus
{
    const IN_CREATION = "status.in_creation";
    const IN_ORGANIZATION = "status.in_organization";
    const AWAITING_VALIDATION="status.awaiting_validation";
    const VALIDATED="status.validated";
    const ARCHIVED="status.archived";
    const DELETED="status.deleted";
}