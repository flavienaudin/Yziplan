<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 02/05/2017
 * Time: 13:19
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Entity\Module\PollModule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("guestsCanAddProposal", CheckboxType::class, array(
                'required' => false
            ))
            ->add('votingType', PollModuleVotingTypeChoiceType::class, array());
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var PollModule $pollModule */
            $pollModule = $formEvent->getData();
            /** @var FormInterface $form */
            $form = $formEvent->getForm();
            $form->add('oldVotingType', HiddenType::class, array(
                'mapped' => false,
                'data' => $pollModule->getVotingType()
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollModule::class
        ));
    }
}