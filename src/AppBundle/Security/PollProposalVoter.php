<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 12:40
 */

namespace AppBundle\Security;

use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollProposal;
use AppBundle\Utils\enum\ModuleInvitationStatus;
use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PollProposalVoter extends Voter
{
    const EDIT = 'pollProposal.edit';
    const DELETE = 'pollProposal.delete';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT, self::DELETE))) {
            return false;
        }
        return is_array($subject) && count($subject) == 2 && ($subject[0] instanceof PollProposal) && ($subject[1] instanceof ModuleInvitation);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var AccountUser $user */
        $user = $token->getUser();
        /** @var PollProposal $pollProposal */
        $pollProposal = $subject[0];// $subject must be a PollProposal instance, thanks to the supports method
        /** @var ModuleInvitation $userModuleInvitation */
        $userModuleInvitation = $subject[1]; // $subject must be a ModuleInvitation instance, thanks to the supports method

        if ($user != null && $user != 'anon.' && !$user instanceof UserInterface) {
            return false;
        }

        dump($userModuleInvitation->getStatus());
        dump(ModuleInvitationStatus::CANCELLED);
        if($userModuleInvitation->getStatus() == ModuleInvitationStatus::CANCELLED){
            return false;
        }
dump("passé ...");
        if ($userModuleInvitation->getModule()->getPollModule() != $pollProposal->getPollModule()) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $pollProposal->getCreator() == $userModuleInvitation || $userModuleInvitation->getEventInvitation()->isOrganizer() || $userModuleInvitation->isOrganizer();
                break;
        }
        return false;
    }

}