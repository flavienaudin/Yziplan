<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/09/2016
 * Time: 17:53
 */

namespace AppBundle\Utils\enum;


class TransactionStatus extends AbstractEnumType
{
    /* Values */
    const DONE = "transactionstatus.done";
    const TO_DO = "transactionstatus.todo";
    const FAILED = "transactionstatus.failde";
    const CANCELLED = "transactionstatus.cancelled";

    protected $name = 'enum_transaction_status';
    protected $values = array(self::DONE, self::TO_DO, self::FAILED, self::CANCELLED);
}