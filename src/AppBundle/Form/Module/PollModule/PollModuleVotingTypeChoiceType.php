<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/05/2017
 * Time: 12:03
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Utils\enum\PollModuleVotingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollModuleVotingTypeChoiceType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'expanded' => true,
            'multiple ' => false,
            'choices' => array(
                PollModuleVotingType::YES_NO_MAYBE => PollModuleVotingType::YES_NO_MAYBE,
                PollModuleVotingType::YES_NO => PollModuleVotingType::YES_NO,
                PollModuleVotingType::SCORING => PollModuleVotingType::SCORING,
                PollModuleVotingType::AMOUNT => PollModuleVotingType::AMOUNT
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}