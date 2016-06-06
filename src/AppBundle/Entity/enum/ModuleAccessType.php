<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 06/06/2016
 * Time: 16:52
 */

namespace AppBundle\Entity\enum;


/**
 * Définie les niveau d'accès pour un evenement ou un module
 *
 * PUBLIC : Tout le monde peut y acceder
 * PRIVATE : réservé aux personnes ayant recu une invitation par mail, si un module est privé il a sa propre liste d'invité et celle-ci ne dépend plus de l'event
 * INHERIT : module seulement, toutes les personnes ayant accès à l'event ont accès au module
 *
 * Class ModuleAccessType
 * @package AppBundle\Entity\enum
 */
class ModuleAccessType
{
    const PUBLIC_ACCESS = "PUBLIC";
    const PRIVATE_ACCESS = "PRIVATE";
    const INHERIT_ACCESS = "INHERIT";
}