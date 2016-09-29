<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 26/07/2016
 * Time: 11:33
 */

namespace AppBundle\Utils\enum;


class PollModuleSortingType extends AbstractEnumType
{
    /** Réponse => yes/no */
    const YES_NO = "yes_no";
    /** Réponse => yes/no/maybe */
    const YES_NO_MAYBE = "yes_no_maybe";
    /** Réponse => nombre entier = note attribuée */
    const NOTATION = "notation";
    /** Réponse => classement des réponses entre elles  */
    const RANKING = "ranking";

    protected $name = 'enum_pollmodule_sortingtype';
    protected $values = array(self::YES_NO, self::YES_NO_MAYBE, self::NOTATION, self::RANKING);
}