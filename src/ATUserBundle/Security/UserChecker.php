<?php

namespace ATUserBundle\Security;

use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use FOS\UserBundle\Model\User;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserChecker vérifie les paramètres du compte utilisateur
 */
class UserChecker extends BaseUserChecker
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        try {
            parent::checkPreAuth($user);
        } catch (DisabledException $disExp) {
            if (!$user instanceof User) {
                throw $disExp;
            }
            // Si l'utilisateur est désactivé parce qu'il a été créé automatiquement lors d'une invitation (par exemple)
            if (empty($user->getConfirmationToken())) {
                // L'utilisateur est désactivé et son confirmationToken est vide (non en attente de confirmation par email)
                $ex = new BadCredentialsException();
                throw $ex;
            }
            throw $disExp;
        }
    }
}
