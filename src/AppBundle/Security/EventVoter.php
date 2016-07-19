<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/02/2016
 * Time: 16:09
 */

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Event;
use ATUserBundle\Entity\User;

class EventVoter extends Voter
{
    const EDIT = 'edit';
    const ADD_EVENT_MODULE = 'addEventModule';
    const VALIDATE = 'validate';
    const ARCHIVE = 'archive';
    const CANCEL = 'cancel';


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT, self::ADD_EVENT_MODULE, self::VALIDATE, self::ARCHIVE, self::CANCEL))) {
            return false;
        }
        if (!$subject instanceof Event) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var Event $event */
        $event = $subject; // $subject must be a Event instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::ADD_EVENT_MODULE:
                if ($event->isGuestsCanAddModule()) {
                    return true;
                }
                return $this->canEdit($event, $user);
                break;
            case self::VALIDATE:
            case self::CANCEL:
            case self::ARCHIVE:
            case self::EDIT:
                return $this->canEdit($event, $user);
                break;
        }
        return false;
    }

    private function canEdit(Event $event, User $user)
    {
        if ($event->getCreator() == null || $event->getCreator()->getAppUser() == null || !$event->getCreator()->getAppUser()->getUser()->isEnabled()) {
            return true;
        } else if ($user == $event->getCreator()->getAppUser()->getUser()) {
            return true;
        }
        return false;
    }
}