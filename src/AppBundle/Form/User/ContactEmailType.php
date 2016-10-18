<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 17:00
 */

namespace AppBundle\Form\User;


use AppBundle\Entity\User\ContactEmail;
use AppBundle\Form\Type\ContactInfoTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, array(
                'required' => false,
            ))
            ->add('type', ContactInfoTypeType::class, array(
                'required' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContactEmail::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'contact_email';
    }
}