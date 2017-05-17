<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 26/07/2016
 * Time: 11:33
 */

namespace AppBundle\Utils\enum;


class PollModuleVotingType extends AbstractEnumType
{
    /** Réponse => yes/no */
    const YES_NO = "pollmodule_votingtype.yes_no";
    /** Réponse => yes/no/maybe */
    const YES_NO_MAYBE = "pollmodule_votingtype.yes_no_maybe";
    /** Réponse => Note sur chaque réponse */
    const SCORING = "pollmodule_votingtype.scoring";
    /** Réponse => classement des réponses entre elles  */
    const RANKING = "pollmodule_votingtype.ranking";
    /** Réponse => nombre entier positif avec ou sans objectif, strict ou dépassable */
    const AMOUNT = "pollmodule_votingtype.amount";

    protected $name = 'enum_pollmodule_votingtype';
    protected $values = array(self::YES_NO, self::YES_NO_MAYBE, self::SCORING, self::RANKING, self::AMOUNT);
}