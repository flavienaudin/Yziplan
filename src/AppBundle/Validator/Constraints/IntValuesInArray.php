<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 02/02/2017
 * Time: 17:26
 */

namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class IntValuesInArray extends Constraint
{
    public $messageInvalidFormat = 'global.validator.constraint.as_array';
    public $messageNotNull = 'global.validator.constraint.values_not_null';
    private $format;
    private $keys;
    private $nullable;

    public function __construct($format, $keys, $nullable = true, $options = null)
    {
        parent::__construct($options);
        $this->format = $format;
        $this->keys = $keys;
        $this->nullable = $nullable;
    }

    /**
     * @return mixed|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return mixed
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }
}