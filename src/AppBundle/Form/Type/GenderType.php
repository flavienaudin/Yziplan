<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:14
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                Gender::MALE => Gender::MALE,
                Gender::FEMALE => Gender::FEMALE,
                Gender::OTHER => Gender::OTHER
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}