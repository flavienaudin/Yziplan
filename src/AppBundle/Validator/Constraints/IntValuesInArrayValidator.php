<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 02/02/2017
 * Time: 17:33
 */

namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IntValuesInArrayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof IntValuesInArray) {
            $dateFormat = $constraint->getFormat();
            if (!is_array($value)) {
                $this->context->buildViolation($constraint->messageInvalidFormat)
                    ->setParameter('%format%', $dateFormat)
                    ->setTranslationDomain("messages")
                    ->addViolation();
                return;
            }
            foreach ($constraint->getKeys() as $key) {
                if (!isset($value[$key])) {
                    $this->context->buildViolation($constraint->messageInvalidFormat)
                        ->setParameter('%format%', $dateFormat)
                        ->setTranslationDomain("messages")
                        ->addViolation();
                    return;
                }
                if (!$constraint->isNullable() && $value[$key] == "") {
                    $this->context->buildViolation($constraint->messageNotNull)
                        ->setTranslationDomain("messages")
                        ->addViolation();
                    return;
                }
            }
        }else{
            $this->context->buildViolation($constraint->messageInvalidFormat)
                ->setParameter('%format%', null)
                ->setTranslationDomain("messages")
                ->addViolation();
            return;
        }
    }
}
