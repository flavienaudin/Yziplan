<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 15:30
 */

namespace AppBundle\Utils\enum;


class KittyParticipationStatus extends AbstractEnumType
{
    const OK = "kittyparticipationstatus.ok";
    const PENDING = "kittyparticipationstatus.pending";
    const CANCELLED = "kittyparticipationstatus.cancelled";

    protected $name = 'enum_kittymodule_objectivetype';
    protected $values = array(null, self::OK, self::PENDING, self::CANCELLED);
}