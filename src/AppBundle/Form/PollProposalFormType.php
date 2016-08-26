<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form;

use AppBundle\Entity\enum\PollProposalElementType;
use AppBundle\Entity\module\PollProposal;
use AppBundle\Entity\module\PollProposalElement;
use Burgov\Bundle\KeyValueFormBundle\Form\Type\KeyValueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollProposalFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, array(
                'required' => true
            ))
            ->add("description", TextareaType::class, array(
                'required' => false
            ))
            ->add("strPPElts", KeyValueType::class, array(
                'value_type' => TextType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'delete_empty' => true,
                'prototype_name' => '__str_ppe__',
                ''

            ))
            ->add("intPPElts", KeyValueType::class, array(
                'value_type' => IntegerType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'delete_empty' => true,
                'prototype_name' => '__int_ppe__'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}