<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 15:59
 */

namespace AppBundle\Utils\enum;


class MaritalStatus extends AbstractEnumType
{
    /* Values */
    const SINGLE = "marital_status.single";
    const COUPLE = "marital_status.couple";
    const MARRIED = "marital_status.married";
    const WIDOWED = "marital_status.widowed";

    protected $name = 'enum_marital_status';
    protected $values = array(null, self::SINGLE, self::COUPLE, self::MARRIED, self::WIDOWED);

}