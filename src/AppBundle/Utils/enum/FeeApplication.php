<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 28/09/2016
 * Time: 17:25
 */

namespace AppBundle\Utils\enum;


class FeeApplication extends AbstractEnumType
{
    /* Values */
    const PAYIN = "feeapplication.payin"; // frais appliqués au moment du credit duu e-wallet
    const PAYOUT = "feeapplication.payout"; // frais appliqués au moment du retrait de l'argent
    const TRANSACTION = "feeapplication.transaction"; // frais appliqué au moment du transfert de l'argent d'un e-wallet à un autre = "status.awaiting_validation";


    protected $name = 'enum_payment_feeapplication';
    protected $values = array(self::PAYIN, self::PAYOUT, self::TRANSACTION);
}