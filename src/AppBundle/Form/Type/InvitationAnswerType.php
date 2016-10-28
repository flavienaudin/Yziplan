<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/09/2016
 * Time: 15:21
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\EventInvitationAnswer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationAnswerType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'required' => true,
            'expanded' => true,
            'choices' => array(
                EventInvitationAnswer::DONT_KNOW => EventInvitationAnswer::DONT_KNOW,
                EventInvitationAnswer::YES => EventInvitationAnswer::YES,
                EventInvitationAnswer::INTERESTED => EventInvitationAnswer::INTERESTED,
                EventInvitationAnswer::NO => EventInvitationAnswer::NO
            ),
            'prefered_choice' => EventInvitationAnswer::DONT_KNOW
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}