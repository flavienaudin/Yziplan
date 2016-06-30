<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 29/06/2016
 * Time: 17:39
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, array(
                'required' => false
            ))
            ->add("description", TextareaType::class, array(
                'required' => false
            ))
            ->add("responseDeadline", DateTimeType::class, array(
                "required" => false,
                'html5' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Module'
        ));
    }

}