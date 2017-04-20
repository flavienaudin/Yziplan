<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 20/04/2017
 * Time: 14:36
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\KittyObjectiveTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KittyObjectiveType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                KittyObjectiveTypeEnum::INDICATIVE => KittyObjectiveTypeEnum::INDICATIVE,
                KittyObjectiveTypeEnum::STRICT => KittyObjectiveTypeEnum::STRICT
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}