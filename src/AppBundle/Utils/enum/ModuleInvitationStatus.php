<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/07/2016
 * Time: 11:03
 */

namespace AppBundle\Utils\enum;


class ModuleInvitationStatus extends AbstractEnumType
{
    /** L'invité participe au module */
    const INVITED = "moduleinvitationstatus.invited";
    /** L'invité ne participe pas "encore" au module (exemple : valeur par défault avant publication du module */
    const NOT_INVITED = "moduleinvitationstatus.not_invited";
    /** L'invité est exclus de participer au module */
    const EXCLUDED = "moduleinvitationstatus.excluded";

    protected $name = 'enum_moduleinvitation_status';
    protected $values = array(self::INVITED, self::NOT_INVITED, self::EXCLUDED);
}