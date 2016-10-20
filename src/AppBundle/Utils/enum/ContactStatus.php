<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/06/2016
 * Time: 15:15
 */

namespace AppBundle\Utils\enum;


class ContactStatus extends AbstractEnumType
{
    /* Values */
    const SUGGESTED = "contactstatus.suggested";
    const VALID = "contactstatus.valid";
    const DELETED = "contactstatus.deleted";

    protected $name = 'enum_contact_status';
    protected $values = array(self::SUGGESTED, self::VALID, self::DELETED);
}