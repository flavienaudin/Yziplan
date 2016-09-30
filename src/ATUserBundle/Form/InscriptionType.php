<?php

namespace ATUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 07/01/2016
 * Time: 14:45
 */
class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'mapped' => false
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ATUserBundle\Entity\AccountUser',
            'csrf_token_id' => 'registration',
        ));
    }

    public function getBlockPrefix()
    {
        return 'at_user_registration';
    }


    // BC for SF < 3.0
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}