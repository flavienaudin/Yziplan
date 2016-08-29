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
    // Text value
    const STRING = "string";
    // Number value
    const INTEGER = "integer";
    // Datetime value
    const DATE_TIME = "datetime";

    // TODO : futur types
    // const FLOAT = "float";
    // const BOOLEAN = "boolean";
    // Place value
    // const PLACE = "place";
}