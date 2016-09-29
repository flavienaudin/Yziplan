<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/09/2016
 * Time: 18:37
 */

namespace AppBundle\Utils\enum;


class ExpenseElementType extends AbstractEnumType
{
    /* Values */
    const EXPENSE = "expense_element_type.expense";
    const REPAYMENT = "expense_element_type.repayment";

    protected $name = 'enum_expenseelement_type';
    protected $values = array(self::EXPENSE, self::REPAYMENT);
}