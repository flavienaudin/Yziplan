<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 26/07/2016
 * Time: 11:33
 */

namespace AppBundle\Entity\enum;


class PollModuleSortingType
{
    /** Réponse => yes/no */
    const YES_NO = "yes_no";
    /** Réponse => yes/no/maybe */
    const YES_NO_MAYBE = "yes_no_maybe";
    /** Réponse => nombre entier = note attribuée */
    const NOTATION = "notation";
    /** Réponse => classement des réponses entre elles  */
    const RANKING  = "ranking";
}