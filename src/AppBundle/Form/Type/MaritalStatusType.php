<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:17
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\MaritalStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaritalStatusType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                MaritalStatus::SINGLE => MaritalStatus::SINGLE,
                MaritalStatus::COUPLE => MaritalStatus::COUPLE,
                MaritalStatus::MARRIED => MaritalStatus::MARRIED
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}