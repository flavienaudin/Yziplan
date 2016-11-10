<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 10/11/2016
 * Time: 15:51
 */

namespace AppBundle\Utils\enum;


class PollProposalResponse extends AbstractEnumType
{
    const YES = "pollproposalresponse.yes";
    const NO = "pollproposalresponse.no";
    const MAYBE = "pollproposalresponse.maybe";

    protected $name = 'enum_pollproposal_response';
    protected $values = array(self::YES, self::NO, self::MAYBE);
}