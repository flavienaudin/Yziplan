<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/09/2016
 * Time: 15:11
 */

namespace AppBundle\Utils\enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractEnumType extends Type
{
    protected $name;
    protected $values = array();

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return "ENUM(" . implode(", ", $values) . ") COMMENT '(DC2Type:" . $this->name . ")'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '" . $this->name . "' value = ".$value);
        }
        return $value;
    }

    public function getName()
    {
        return $this->name;
    }

}