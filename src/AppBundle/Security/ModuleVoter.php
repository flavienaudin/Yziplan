<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 12:40
 */

namespace AppBundle\Security;


use AppBundle\Entity\Module;
use ATUserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ModuleVoter extends Voter
{
    const DISPLAY = 'display';
    const EDIT = 'edit';
    const VALIDATE = 'validate';
    const ARCHIVE = 'archive';
    const CANCEL = 'cancel';
    const DELETE = 'delete';


    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::DISPLAY, self::EDIT, self::VALIDATE, self::ARCHIVE, self::CANCEL, self::DELETE))) {
            return false;
        }
        if (!$subject instanceof Module) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var Module $module */
        $module = $subject; // $subject must be a Module instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }
        $event= $module->getEvent();;
        switch ($attribute) {
            case self::DISPLAY:
                // TODO Controler l'access (si evenement privée, invitation nécessaire...)
                return true;
                break;
            case self::VALIDATE:
            case self::CANCEL:
            case self::ARCHIVE:
            case self::EDIT:
            case self::DELETE:
                if ($event->getCreator() == null || $event->getCreator()->getAppUser() == null || !$event->getCreator()->getAppUser()->getUser()->isEnabled()) {
                    return true;
                } else if ($user == $event->getCreator()->getAppUser()->getUser()) {
                    return true;
                }
                break;
        }
        return false;
    }

}