<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 09/06/2016
 * Time: 10:41
 */

namespace ATUserBundle\Form;


use AppBundle\Entity\User\AppUserInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppUserInformationBiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("biography", TextareaType::class, array(
                "required" => false
            ));
    }

    public function getBlockPrefix()
    {
        return 'appuserinfo_biography';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AppUserInformation::class,
            'csrf_token_id' => 'appUserInfoBiography'
        ));
    }
}