<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/02/2016
 * Time: 16:09
 */

namespace AppBundle\Security;

use ATUserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\EventInvitation;

class EventInvitationVoter extends Voter
{
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT))) {
            return false;
        }
        if (!$subject instanceof EventInvitation) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $utilisateur */
        $utilisateur = $token->getUser();
        /** @var EventInvitation $invitation */
        $invitation = $subject; // $subject must be a Invitation instance, thanks to the supports method
        if ($utilisateur != null && $utilisateur != 'anon.' && !$utilisateur instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::EDIT:
                if ($invitation->getAppUser()->getUser() == null || !$invitation->getAppUser()->getUser()->isEnabled()) {
                    return true;
                } elseif ($utilisateur == $invitation->getAppUser()->getUser()) {
                    return true;
                }
                break;
        }
        return false;
    }
}