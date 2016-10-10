<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 09/06/2016
 * Time: 10:41
 */

namespace ATUserBundle\Form;


use AppBundle\Entity\User\AppUserInformation;
use AppBundle\Utils\enum\MaritalStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppUserInfoComplementariesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("maritalStatus", ChoiceType::class, array(
                "required" => false,
                "choices" => array(
                    MaritalStatus::SINGLE => MaritalStatus::SINGLE,
                    MaritalStatus::COUPLE => MaritalStatus::COUPLE,
                    MaritalStatus::MARRIED => MaritalStatus::MARRIED
                )
            ))
            ->add("biography", TextareaType::class, array(
                "required" => false
            ))
            ->add("interests", TextareaType::class, array(
                "required" => false
            ))
            ->add("foodConveniences", TextareaType::class, array(
                "required" => false
            ));
    }

    public function getBlockPrefix()
    {
        return 'appuserinfo_complementaries';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AppUserInformation::class,
            'csrf_token_id' => 'appuserinfo_complementaries'
        ));
    }
}