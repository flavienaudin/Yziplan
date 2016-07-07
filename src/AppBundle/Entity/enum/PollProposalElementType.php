<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 15:30
 */

namespace AppBundle\Entity\enum;


class PollProposalElementType
{
    const BOOLEAN = "Boolean";
    const STRING = "String";
    // Pour le stockage des dates
    const TIME_STAMP = "TimeStamp";
    const INTEGER = "Integer";
    const FLOAT = "Float";
    /// Pour les lieux
    const PLACE = "Place";
}