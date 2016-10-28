<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 19/10/2016
 * Time: 17:39
 */

namespace AppBundle\Security;


use AppBundle\Entity\User\Contact;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ContactVoter extends Voter
{
    const EDIT = 'contact.edit';
    const DELETE = 'contact.delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT, self::DELETE))) {
            return false;
        }
        return $subject instanceof Contact;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var Contact $contact */
        $contact = $subject; // $subject must be a Contact instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $contact->getOwner() == $user->getApplicationUser();
                break;
        }
        return false;
    }
}