<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form\Module;

use AppBundle\Entity\Module\PollProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var PollProposal $pollProposal */
                $pollProposal = $formEvent->getData();

                /** @var FormInterface $form */
                $form = $formEvent->getForm();
                $form->add("pollProposalElements", CollectionType::class, array(
                    'entry_type' => PollProposalElementType::class,
                    'required' => false,
                    'mapped' => true,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true
                ));
                if (($pollProposal != null) && !empty($pollProposal->getId())) {
                    $form->add('id', HiddenType::class, array(
                            'disabled' => true
                        ));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }

}