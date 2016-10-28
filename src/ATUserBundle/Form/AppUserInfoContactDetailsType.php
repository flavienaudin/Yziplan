<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/10/2016
 * Time: 16:54
 */

namespace ATUserBundle\Form;


use AppBundle\Entity\User\AppUserInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppUserInfoContactDetailsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries = array_flip(Intl::getRegionBundle()->getCountryNames());
        $builder
            ->add('livingCountry', ChoiceType::class, array(
                'required' => false,
                'choices' => $countries
            ))
            ->add('livingCity', TextType::class, array(
                'required' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AppUserInformation::class,
            'csrf_token_id' => 'appsUserContactDetails'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appuserinfo_contactdetails';
    }
}