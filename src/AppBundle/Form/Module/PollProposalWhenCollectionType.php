<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use AppBundle\Entity\Module\PollModule;
use AppBundle\Entity\Module\PollProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class PollProposalWhenCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("pollProposalWhenElements", CollectionType::class, array(
            'entry_type' => PollProposalWhenElementType::class,
            'allow_add' => true,
            'required' => false,
            'mapped' => false,
            'allow_delete' => true,
            'prototype' => true
        ));
    }
}