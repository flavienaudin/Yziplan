<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 20/10/2016
 * Time: 16:31
 */

namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class EmailNotBelongToAccountUser extends Constraint
{
    public $message = "eventInvitation.message.warning.email_owned_by_accountuser";
}