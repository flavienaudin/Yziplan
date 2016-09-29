<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 16:18
 */

namespace AppBundle\Utils\enum;


class LegalStatus extends AbstractEnumType
{
    /* Values */
    const INDIVIDUAL = "legal_status.individual";
    const ORGANISATION = "legal_status.organisation";

    protected $name = 'enum_legal_status';
    protected $values = array(self::INDIVIDUAL, self::ORGANISATION);
}