<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 27/07/2016
 * Time: 16:02
 */

namespace AppBundle\Security;


use AppBundle\Entity\ModuleInvitation;
use ATUserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleInvitationVoter extends Voter
{
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT))) {
            return false;
        }
        if (!$subject instanceof ModuleInvitation) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        /** @var ModuleInvitation $moduleInvitation */
        $moduleInvitation = $subject; // $subject must be a ModuleInvitation instance, thanks to the supports method

        switch ($attribute) {
            case self::EDIT:
                if ($moduleInvitation->getEventInvitation()->getAppUser() == null || !$moduleInvitation->getEventInvitation()->getAppUser()->getUser()->isEnabled()) {
                    return true;
                } elseif ($moduleInvitation->getEventInvitation()->getAppUser()->getUser() == $user) {
                    return true;
                }
                return false;
                break;
        }
        return false;
    }
}