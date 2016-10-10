<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:32
 */

namespace AppBundle\Utils\enum;

/**
 * Class FlashBagTypes
  *
 * Types autorisés pour les messages du flashbag
 */
class FlashBagTypes extends AbstractEnumType
{
    const SUCCESS_TYPE = "success";
    const INFO_TYPE = "info";
    const WARNING_TYPE = "warning";
    const ERROR_TYPE = "error";

    protected $name = 'enum_module_type';
    protected $values = array(self::SUCCESS_TYPE, self::INFO_TYPE, self::WARNING_TYPE, self::ERROR_TYPE);
}