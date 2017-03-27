<?php
/**
 * User: Patrick
 * Date: 27/03/2017
 * Time: 15:03
 */
namespace AppBundle\Form\Module\PollModule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class PollProposalWhenCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("pollProposalWhens", CollectionType::class, array(
            'entry_type' => PollProposalWhenType::class,
            'allow_add' => true,
            'required' => false,
            'mapped' => false,
            'allow_delete' => true,
            'prototype' => true
        ));
    }
}