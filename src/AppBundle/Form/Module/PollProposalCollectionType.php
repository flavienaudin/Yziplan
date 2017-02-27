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

class PollProposalCollectionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("pollProposals", CollectionType::class, array(
                'entry_type' => PollProposalType::class,
                'required' => false,
                'mapped' => true,
                'by_reference' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}