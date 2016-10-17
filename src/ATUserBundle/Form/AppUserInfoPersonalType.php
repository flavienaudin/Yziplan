<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 17:19
 */

namespace ATUserBundle\Form;


use AppBundle\Entity\User\AppUserInformation;
use AppBundle\Utils\enum\Gender;
use AppBundle\Utils\enum\LegalStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class AppUserInfoPersonalType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('legalStatus', ChoiceType::class, array(
                'required' => false,
                'choices' => array(
                    LegalStatus::INDIVIDUAL => LegalStatus::INDIVIDUAL,
                    LegalStatus::ORGANISATION => LegalStatus::ORGANISATION,
                )
            ))
            ->add('publicName', TextType::class, array(
                'required' => true,
                'constraints' => array(
                    new NotBlank(), new Length(array('min'=> 2))
                )
            ))
            ->add('firstName', TextType::class, array(
                'required' => false,
            ))
            ->add('lastName', TextType::class, array(
                'required' => false,
            ))
            ->add('gender', ChoiceType::class, array(
                'required' => false,
                'choices' => array(
                    Gender::MALE => Gender::MALE,
                    Gender::FEMALE => Gender::FEMALE,
                    Gender::OTHER => Gender::OTHER
                )
            ))
            ->add('birthday', DateType::class, array(
                'required' => false,
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'constraints' => array(
                    new LessThan("today")
                )
            ))
            ->add('nationality', TextType::class, array(
                'required' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AppUserInformation::class,
            'csrf_token_id' => 'appsUserInformation'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appuserinfo_personals';
    }
}