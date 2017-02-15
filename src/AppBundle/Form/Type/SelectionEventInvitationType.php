<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:14
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\EventInvitationAnswer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectionEventInvitationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'expanded' => true,
            'multiple ' => true,
            'choices' => array(
                EventInvitationAnswer::YES => EventInvitationAnswer::YES,
                EventInvitationAnswer::DONT_KNOW => EventInvitationAnswer::DONT_KNOW,
                EventInvitationAnswer::NO => EventInvitationAnswer::NO,
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}