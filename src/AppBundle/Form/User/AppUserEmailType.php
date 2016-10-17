<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/10/2016
 * Time: 14:58
 */

namespace AppBundle\Form\User;

use AppBundle\Entity\User\AppUserEmail;
use AppBundle\Form\Type\ContactInfoTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('type', ContactInfoTypeType::class, array(
                'required' => false
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var AppUserEmail $appUserEmail */
                $appUserEmail = $formEvent->getData();
                $form = $formEvent->getForm();
                if (empty($appUserEmail->getId())) {
                    $form->add('email', EmailType::class, array('required' => true));
                }
                if ($appUserEmail->getApplicationUser() == null || $appUserEmail->getApplicationUser()->getAccountUser() == null
                    || $appUserEmail->getEmailCanonical() != $appUserEmail->getApplicationUser()->getAccountUser()->getEmailCanonical()
                ) {
                    $form->add('useToReceiveEmail', CheckboxType::class, array(
                        'required' => false,
                    ));
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