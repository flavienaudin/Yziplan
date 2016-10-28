<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:22
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\LegalStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegalStatusType extends AbstractType 
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                LegalStatus::INDIVIDUAL => LegalStatus::INDIVIDUAL,
                LegalStatus::ORGANISATION => LegalStatus::ORGANISATION,
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}