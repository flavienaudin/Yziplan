<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/10/2016
 * Time: 10:54
 */

namespace AppBundle\Utils\User;

use AppBundle\Entity\User\AppUserEmail;
use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater as FosCanonicalFieldsUpdater;


class CanonicalFieldsUpdater extends FosCanonicalFieldsUpdater
{

    /**
     * Update the AppUserEmail of the AccountUser
     * @param UserInterface $user
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        parent::updateCanonicalFields($user);
        if ($user instanceof AccountUser) {
            /** @var AppUserEmail $appUserEMail */
            foreach ($user->getApplicationUser()->getAppUserEmails() as $appUserEMail) {
                $appUserEMail->setEmailCanonical($this->canonicalizeEmail($appUserEMail->getEmail()));
            }
        }
    }
}