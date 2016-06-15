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
    const EDITER = 'editer';
    const VALIDER = 'valider';
    const ARCHIVER = 'archiver';
    const DEPROGRAMMER = 'deprogrammer';


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDITER, self::VALIDER, self::ARCHIVER, self::DEPROGRAMMER))) {
            return false;
        }
        if (!$subject instanceof Event) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $utilisateur */
        $utilisateur = $token->getUser();
        /** @var Event $evenement */
        $evenement = $subject; // $subject must be a Event instance, thanks to the supports method

        if ($utilisateur != null && $utilisateur != 'anon.' && !$utilisateur instanceof UserInterface) {
            return false;
        }
        switch ($attribute) {
            case self::VALIDER:
            case self::DEPROGRAMMER:
            case self::ARCHIVER:
            case self::EDITER:
                if ($evenement->getCreator() == null || !$evenement->getCreator()->getAppUser()->getUser()->isEnabled()) {
                    return true;
                } else if ($evenement->getCreator()->getAppUser()->getUser()->isEnabled()
                    && $utilisateur == $evenement->getCreator()->getAppUser()->getUser()
                ) {
                    return true;
                }
                break;
        }
        return false;
    }
}