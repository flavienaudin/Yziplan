<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/09/2016
 * Time: 11:36
 */

namespace AppBundle\Utils\enum;


class ContactInfoType extends AbstractEnumType
{
    /* Values */
    const HOME = "contact_info_type.home";
    const BUSINESS = "contact_info_type.business";

    protected $name = 'enum_contactinfo_type';
    protected $values = array(null, self::HOME, self::BUSINESS);
}