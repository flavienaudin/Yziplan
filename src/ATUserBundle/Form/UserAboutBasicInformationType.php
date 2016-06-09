<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 17:19
 */

namespace ATUserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserAboutBasicInformationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', TextType::class, array(
                "required" => false,
            ))
            ->add('gender', ChoiceType::class, array(
                "required" => false,
                "choices" => array(
                    "global.gender.1" => 1,
                    "global.gender.2" => 2,
                    "global.gender.3" => 3
                ),
                "choice_translation_domain" => true
            ))
            ->add('birthday', DateType::class, array(
                'required' => false,
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy'
            ));
    }

    public function getBlockPrefix()
    {
        return 'userabout_basicinformation';
    }
}