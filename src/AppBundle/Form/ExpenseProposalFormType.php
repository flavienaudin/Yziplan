<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/06/2016
 * Time: 12:12
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenseProposalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expenseModuleId', HiddenType::class, array(
                'mapped' => false,
                'required' => false
            ))
            ->add('nom', TextType::class, array(
                'label' => 'module.expense.nom',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.expense.nom',
                )))
            ->add('description', TextareaType::class, array(
                'label' => 'module.expense.description',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.expense.description',
                )))
            ->add('amount', NumberType::class, array(
                'label' => 'module.expense.montant',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.expense.montant',
                )))
            ->add('expenseDate', DateTimeType::class, array(
                'label' => 'module.expense.date',
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
                'html5' => false,
            ))
            ->add('place', TextType::class, array(
                'label' => 'module.expense.lieu',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.expense.lieu'
                )))
            ->add('googlePlaceId', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\module\ExpenseProposal'
        ));
    }
}