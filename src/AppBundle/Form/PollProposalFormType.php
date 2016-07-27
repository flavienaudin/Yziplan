<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form;

use AppBundle\Entity\module\PollProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}