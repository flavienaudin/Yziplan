<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 15:30
 */

namespace AppBundle\Entity\enum;

use SplEnum;

class ProposalElementType extends SplEnum
{
    const Boolean = "Boolean";
    const String = "String";
    // Pour le stockage des dates
    const TimeStamp = "TimeStamp";
    const Integer = "Integer";
    const Float = "Float";
    /// Pour les lieux
    const Place = "Place";
}