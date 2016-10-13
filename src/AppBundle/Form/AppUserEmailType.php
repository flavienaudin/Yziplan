<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/10/2016
 * Time: 14:58
 */

namespace AppBundle\Form;


use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Utils\enum\ContactInfoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppUserEmailType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                'required' => false,
                'choices' => array(
                    ContactInfoType::HOME => ContactInfoType::HOME,
                    ContactInfoType::BUSINESS => ContactInfoType::BUSINESS
                )
            ))
            ->add('useToReceiveEmail', CheckboxType::class, array(
                'required' => false,
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var AppUserEmail $appUserEmail */
                $appUserEmail = $formEvent->getData();
                if(empty($appUserEmail->getId())) {
                    $form = $formEvent->getForm();
                    $form->add('email', EmailType::class, array('required' => true));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AppUserEmail::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_user_email';
    }
}