<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 12:40
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\Module;
use AppBundle\Entity\Event\ModuleInvitation;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleVoter extends Voter
{
    const DISPLAY = 'display';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::DISPLAY, self::EDIT, self::DELETE))) {
            return false;
        }
        if (!is_array($subject) && count($subject) != 2) {
            return false;
        }
        return $subject[0] instanceof Module && $subject[1] instanceof ModuleInvitation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var Module $module */
        $module = $subject[0]; // $subject[0] must be a Module instance, thanks to the supports method
        /** @var ModuleInvitation $userModuleInvitation */
        $userModuleInvitation = $subject[1]; // $subject[1] must be a EventInvitation instance, thanks to the supports method

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
                if ($module->getCreator() != null) {
                    if ($module->getCreator() === $userModuleInvitation) {
                        return true;
                    } else if ($module->getCreator()->getEventInvitation()->getApplicationUser() != null && $user == $module->getCreator()->getEventInvitation()->getApplicationUser()->getUser()) {
                        return true;
                    }
                }
                break;
        }
        return false;
    }

}