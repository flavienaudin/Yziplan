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
use AppBundle\Entity\Event\Event;
use ATUserBundle\Entity\AccountUser;

class EventVoter extends Voter
{
    const EDIT = 'edit';
    const ADD_EVENT_MODULE = 'addEventModule';
    const VALIDATE = 'validate';
    const ARCHIVE = 'archive';
    const CANCEL = 'cancel';

    /**
     * @param string $attribute Cf. constants in EventVoter
     * @param mixed $subject Array with {Event, null|request.event.tokenEdition}
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT, self::ADD_EVENT_MODULE, self::VALIDATE, self::ARCHIVE, self::CANCEL))) {
            return false;
        }
        if (!is_array($subject) || count($subject) != 2) {
            return false;
        }
        return ($subject[0] instanceof Event && ($attribute == self::ADD_EVENT_MODULE || is_string($subject[1])));
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var Event $event */
        $event = $subject[0]; // $subject must be a Event instance, thanks to the supports method
        $tokenEdition = $subject[1]; // $subject must be a string, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::ADD_EVENT_MODULE:
                if ($event->isGuestsCanAddModule()) {
                    return true;
                }
                return $this->canEdit($event, $user, $tokenEdition);
                break;
            case self::VALIDATE:
            case self::CANCEL:
            case self::ARCHIVE:
            case self::EDIT:
                return $this->canEdit($event, $user, $tokenEdition);
                break;
        }
        return false;
    }

    private function canEdit(Event $event, $user, $tokenEdition)
    {
        if (!empty($tokenEdition) && $event->getTokenEdition() === $tokenEdition) {
            if ($event->getCreator() == null || $event->getCreator()->getApplicationUser() == null || !$event->getCreator()->getApplicationUser()->getUser()->isEnabled()) {
                return true;
            } else if ($user == $event->getCreator()->getApplicationUser()->getUser()) {
                return true;
            }
        }
        return false;
    }
}