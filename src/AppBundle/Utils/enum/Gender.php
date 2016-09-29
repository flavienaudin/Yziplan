<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 15:01
 */

namespace AppBundle\Utils\enum;


class Gender extends AbstractEnumType
{
    /* Values */
    const MALE = "gender.male";
    const FEMALE = "gender.female";
    const OTHER = "gender.other";

    protected $name = 'enum_gender';
    protected $values = array(self::MALE, self::FEMALE, self::OTHER);
}