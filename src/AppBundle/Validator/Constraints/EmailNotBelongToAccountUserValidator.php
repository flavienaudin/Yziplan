<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 20/10/2016
 * Time: 16:32
 */

namespace AppBundle\Validator\Constraints;


use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Manager\ApplicationUserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailNotBelongToAccountUserValidator extends ConstraintValidator
{
    /** @var ApplicationUserManager */
    private $applicationUserManager;

    public function __construct(ApplicationUserManager $applicationUserManager)
    {
        $this->applicationUserManager = $applicationUserManager;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var AppUserEmail $appUserEmail */
        $appUserEmail = $this->applicationUserManager->findAppUserEmailByEmail($value);
        if ($appUserEmail != null && $appUserEmail->getApplicationUser()->getAccountUser() != null) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain("messages")
                ->addViolation();
        }
    }
}