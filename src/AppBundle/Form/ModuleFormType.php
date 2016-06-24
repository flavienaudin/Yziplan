<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/06/2016
 * Time: 15:20
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'module.nom',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.nom',
                )))
            ->add('description', TextareaType::class, array(
                'label' => 'module.description',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'module.description',
                )))
            ->add('responseDeadline', DateTimeType::class, array(
                'label' => 'module.deadline',
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
                'html5' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Module'
        ));
    }
}