<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:27
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\ContactInfoType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactInfoTypeType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                ContactInfoType::HOME => ContactInfoType::HOME,
                ContactInfoType::BUSINESS => ContactInfoType::BUSINESS
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}