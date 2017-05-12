<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 10/04/2017
 * Time: 16:34
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\InvitationRule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationRuleType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                InvitationRule::EVERYONE => InvitationRule::EVERYONE,
                InvitationRule::NONE_EXCEPT => InvitationRule::NONE_EXCEPT
            ),
            'expanded' => true,
            'multiple' => false
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}