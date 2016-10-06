<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/02/2016
 * Time: 16:09
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\EventInvitation;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    const EDIT = 'edit';
    const ADD_EVENT_MODULE = 'addEventModule';
    const VALIDATE = 'validate';
    const ARCHIVE = 'archive';
    const CANCEL = 'cancel';

    /**
     * @param string $attribute Cf. constants in EventVoter
     * @param mixed $subject EventInvitation
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT, self::ADD_EVENT_MODULE, self::VALIDATE, self::ARCHIVE, self::CANCEL))) {
            return false;
        }
        if (!$subject instanceof EventInvitation) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var EventInvitation $eventInvitation */
        $eventInvitation = $subject; // $subject must be a EventInvitation instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::ADD_EVENT_MODULE:
                if ($eventInvitation->getEvent()->isGuestsCanAddModule()) {
                    return true;
                }
                return $eventInvitation->isCreator() || $eventInvitation->isAdministrator();
                break;
            case self::CANCEL:
            case self::ARCHIVE:
                return $eventInvitation->isCreator();
            case self::VALIDATE:
            case self::EDIT:
                return $eventInvitation->isCreator() || $eventInvitation->isAdministrator();
                break;
        }
        return false;
    }
}