<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 10/04/2017
 * Time: 16:31
 */

namespace AppBundle\Form\Notifications;


use AppBundle\Entity\Notifications\EventInvitationPreferences;
use AppBundle\Form\Type\NotificationFrequencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInvitationNotificationPreferencesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notifEmailFrequency', NotificationFrequencyType::class, array(
                'required' => true
            ))
            ->add('notifNewComment', CheckboxType::class, array(
                'required' => false
            ))
            ->add('notifNewModule', CheckboxType::class, array(
                'required' => false
            ))
            ->add('notifNewPollpropsal', CheckboxType::class, array(
                'required' => false
            ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventInvitationPreferences::class
        ));
    }
}