<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 12:40
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\ModuleInvitation;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleVoter extends Voter
{
    const DISPLAY = 'module.display';
    const EDIT = 'module.edit';
    const DELETE = 'module.delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::DISPLAY, self::EDIT, self::DELETE))) {
            return false;
        }
        return $subject instanceof ModuleInvitation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var ModuleInvitation $userModuleInvitation */
        $userModuleInvitation = $subject; // $subject must be a EventInvitation instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::DISPLAY:
                // TODO Controler l'access (si evenement privée, invitation nécessaire...)
                return true;
                break;
            case self::EDIT:
            case self::DELETE:
                return $userModuleInvitation->isCreator() || $userModuleInvitation->isAdministrator();
                break;
        }
        return false;
    }

}