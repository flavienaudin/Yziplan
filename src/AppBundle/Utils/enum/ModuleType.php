<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 06/07/2016
 * Time: 14:51
 */

namespace AppBundle\Utils\enum;


class ModuleType extends AbstractEnumType
{
    const POLL_MODULE = 'PollModule';
    const EXPENSE_MODULE = 'ExpenseModule';

    protected $name = 'enum_module_type';
    protected $values = array(self::POLL_MODULE, self::EXPENSE_MODULE);
}