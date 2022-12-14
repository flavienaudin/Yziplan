<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 15:30
 */

namespace AppBundle\Utils\enum;


class PollElementType extends AbstractEnumType
{

    const GOOGLE_PLACE_ID ="googlePlaceId";
    const RICHTEXT = "richText";
    const PICTURE = "picture";
    // Values already in AbstractEnumType's constantes

    protected $name = 'enum_pollproposal_elementtype';
    protected $values = array(self::STRING, self::INTEGER, self::DATETIME, self::GOOGLE_PLACE_ID, self::RICHTEXT, self::PICTURE);

}