<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/02/2016
 * Time: 12:47
 */

namespace ATUserBundle\Form;

use ATUserBundle\Entity\AccountUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'required' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccountUser::class,
            'csrf_token_id' => 'profile'
        ));
    }

    public function getBlockPrefix()
    {
        return 'user_profile';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}