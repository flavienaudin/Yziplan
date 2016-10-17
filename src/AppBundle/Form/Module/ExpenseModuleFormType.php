<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/06/2016
 * Time: 14:50
 */

namespace AppBundle\Form\Module;

use AppBundle\Entity\Module\ExpenseModule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenseModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('module', ModuleFormType::class, array(
                'required' => true
            ))
            ->add('expenseElements', CollectionType::class, array(
                'label_attr' => array('class' => 'sr-only'),
                'block_name' => 'expenseElements',
                'entry_type' => ExpenseElementFormType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ExpenseModule::class
        ));
    }
}