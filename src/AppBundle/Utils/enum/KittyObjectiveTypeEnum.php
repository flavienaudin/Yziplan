<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 15:30
 */

namespace AppBundle\Utils\enum;


class KittyObjectiveTypeEnum extends AbstractEnumType
{
    const STRICT = "kittyobjectivetype.strict";
    const INDICATIVE = "kittyobjectivetype.indicative";

    protected $name = 'enum_kittymodule_objectivetype';
    protected $values = array(null, self::STRICT, self::INDICATIVE);

}