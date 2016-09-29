<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/02/2016
 * Time: 16:09
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\Event;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\EventInvitation;

class EventInvitationVoter extends Voter
{
    /** When displaying event without a personal invitation (public event) */
    const CREATE = 'create';
    /** When guest want to invite */
    const INVITE = 'invite';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CREATE, self::INVITE, self::EDIT))) {
            return false;
        }
        if ($attribute == self::EDIT && !$subject instanceof EventInvitation) {
            return false;
        }
        if (($attribute == self::CREATE || $attribute == self::INVITE) && !$subject instanceof Event) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }

        if ($attribute == self::EDIT) {
            /** @var EventInvitation $eventInvitation */
            $eventInvitation = $subject; // $subject must be a Invitation instance, thanks to the supports method
            if($eventInvitation->getApplicationUser() == null){
                return true;
            }else if ($eventInvitation->getApplicationUser()->getUser() == null || !$eventInvitation->getApplicationUser()->getUser()->isEnabled()) {
                return true;
            } elseif ($user == $eventInvitation->getApplicationUser()->getUser()) {
                return true;
            }
        } else {
            /** @var Event $event */
            $event = $subject; // $subject must be a Event instance, thanks to the supports method
            switch ($attribute) {
                case self::CREATE:
                    return !$event->isInvitationOnly();
                case self::INVITE:
                    return $event->isGuestsCanInvite();
            }
        }
        return false;
    }
}