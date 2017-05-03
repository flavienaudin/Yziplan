<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 12:40
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PollProposalVoter extends Voter
{
    const ADD = 'pollProposal.add';
    const EDIT = 'pollProposal.edit';
    const DELETE = 'pollProposal.delete';

    protected function supports($attribute, $subject)
    {
        if (!is_array($subject) || count($subject) != 2) {
            return false;
        }
        if ($attribute === self::ADD) {
            return ($subject[0] instanceof PollModule) && ($subject[1] instanceof ModuleInvitation);
        } elseif ($attribute === self::EDIT || $attribute === self::DELETE) {
            return ($subject[0] instanceof PollProposal) && ($subject[1] instanceof ModuleInvitation);
        }
        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var ModuleInvitation $userModuleInvitation */
        $userModuleInvitation = $subject[1]; // $subject must be a ModuleInvitation instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }

        if ($userModuleInvitation->getStatus() != ModuleInvitationStatus::INVITED) {
            return false;
        }

        switch ($attribute) {
            case self::ADD:
                /** @var PollModule $pollModule */
                $pollModule = $subject[0];  // $subject must be a PollModule instance, thanks to the supports method
                if ($userModuleInvitation->getModule()->getPollModule() !== $pollModule) {
                    return false;
                }
                return $pollModule->isGuestsCanAddProposal() || $userModuleInvitation->getEventInvitation()->isOrganizer() || $userModuleInvitation->isOrganizer();
                break;
            case self::EDIT:
            case self::DELETE:
                /** @var PollProposal $pollProposal */
                $pollProposal = $subject[0]; // $subject must be a PollProposal instance, thanks to the supports method
                if ($userModuleInvitation->getModule()->getPollModule() !== $pollProposal->getPollModule()) {
                    return false;
                }

                return $pollProposal->getCreator() === $userModuleInvitation || $userModuleInvitation->getEventInvitation()->isOrganizer() || $userModuleInvitation->isOrganizer();
                break;
        }
        return false;
    }

}