<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/06/2016
 * Time: 11:10
 */

namespace AppBundle\Entity\enum;


/**
 * Class EventStatus Défini les états possibles pour un événément (status).
 * @package AppBundle\Entity\enum
 */
class EventStatus
{
    const IN_CREATION = "status.in_creation";
    const IN_ORGANIZATION = "status.in_organization";
    const AWAITING_VALIDATION="status.awaiting_validation";
    const VALIDATED="status.validated";
    const ARCHIVED="status.archived";
    const DEPROGRAMMED="status.deprogrammed";

    /** @var array Liste des états possibles pour un événement */
    //public $STATUS_LIST=array(self::IN_ORGANIZATION, self::AWAITING_VALIDATION, self::VALIDATED, self::ARCHIVED, self::DEPROGRAMMED);
}