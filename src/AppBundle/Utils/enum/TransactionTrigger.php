<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/09/2016
 * Time: 17:41
 */

namespace AppBundle\Utils\enum;


class TransactionTrigger extends AbstractEnumType
{

    /* Values */
    const IMMEDIATE = "transactiontrigger.immediate"; // la transaction est effectuée dès qu'elle est créée (sauf si le wallet à crediter n'a pas de e-wallet mangopay)
    const AT_THE_END = "transactiontrigger.attheend"; // les transactions sont effectuées à la fin de l'événement
    const ON_DEMAND = "transactiontrigger.ondemand"; // les transactions sont effectuées à la demande

    protected $name = 'enum_payment_transactiontrigger';
    protected $values = array(self::IMMEDIATE, self::ON_DEMAND);

}