<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/02/2016
 * Time: 16:09
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventInvitation;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;


class EventInvitationVoter extends Voter
{
    /* When displaying event without a personal invitation (public event)*/
    const CREATE = 'event_invitation.create';
    /* When guest want to invite */
    const INVITE = 'event_invitation.invite';
    /* When user want to access an EventInvitation to edit it */
    const EDIT = 'event_invitation.edit';
    /* To cancel en event invitation : the user's EventInvitation and the EventInvitation to cancel are required */
    const CANCEL = 'event_invitation.cancel';
    /* To cancel en event invitation : the user's EventInvitation and the EventInvitation to cancel are required */
    const ARCHIVE = 'event_invitation.archive';

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CREATE, self::INVITE, self::EDIT, self::CANCEL, self::ARCHIVE))) {
            return false;
        }
        if (($attribute == self::CREATE || $attribute == self::INVITE) && !$subject instanceof Event) {
            return false;
        }
        if (($attribute == self::EDIT || self::ARCHIVE) && !$subject instanceof EventInvitation) {
            return false;
        }
        if ($attribute == self::CANCEL && (!is_array($subject) || count($subject) != 2 || !($subject[0] instanceof EventInvitation) || !($subject[1] instanceof EventInvitation))) {
            return false;
        }
        return true;
    }


    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        if ($user != null && $user != 'anon.' && !$user instanceof AccountUser) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                /** @var Event $event */
                $event = $subject; // $subject must be a Event instance, thanks to the supports method
                return !$event->isInvitationOnly();
            case self::INVITE:
                /** @var Event $event */
                $event = $subject; // $subject must be a Event instance, thanks to the supports method
                return $event->isGuestsCanInvite();
            case self::EDIT:
                /** @var EventInvitation $eventInvitation */
                $eventInvitation = $subject; // $subject must be a Invitation instance, thanks to the supports method
                if ($eventInvitation->getApplicationUser() == null) {
                    return true;
                } else if ($eventInvitation->getApplicationUser()->getAccountUser() == null) {
                    // TODO Ajouter le cas ou l'email de l'invitation n'appartient pas au compte
                    return true;
                } elseif ($user == $eventInvitation->getApplicationUser()->getAccountUser()) {
                    return true;
                }
                return false;
            case self::CANCEL:
                /** @var EventInvitation $userEventInvitation */
                $userEventInvitation = $subject[0]; // $subject[0] must be an EventInvitation instance, thanks to the supports method
                /** @var EventInvitation $userEventInvitationToCancel */
                $userEventInvitationToCancel = $subject[1]; // $subject[0] must be an EventInvitation instance, thanks to the supports method
                if($userEventInvitationToCancel->isCreator()){
                    return false;
                }
                if($userEventInvitationToCancel->isAdministrator() && !$userEventInvitation->isCreator()){
                    return false;
                }
                if($userEventInvitation->isCreator() || $userEventInvitation->isAdministrator()){
                    return true;
                }
                return false;
            case self::ARCHIVE:
                if($user instanceof AccountUser) {
                    /** @var EventInvitation $eventInvitation */
                    $eventInvitation = $subject; // $subject must be a Invitation instance, thanks to the supports method
                    return $eventInvitation->getApplicationUser() == $user->getApplicationUser();
                }
                return false;
        }
        return false;
    }
}