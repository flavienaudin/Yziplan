<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/07/2016
 * Time: 16:31
 */

namespace AppBundle\Form\Event;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Form\Type\InvitationAnswerType;
use AppBundle\Validator\Constraints\EmailNotBelongToAccountUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var Form $form */
            $form = $formEvent->getForm();
            /** @var EventInvitation $eventInvitation */
            $eventInvitation = $formEvent->getData();
            $displayableName = $eventInvitation->getDisplayableName();
            $form
                ->add('guestName', TextType::class, array(
                    'required' => false,
                    'data' => $displayableName

                ))
                ->add('token', HiddenType::class);

            $email = $eventInvitation->getDisplayableEmail();
            $form->add('email', EmailType::class, array(
                'required' => false,
                'mapped' => false,
                'data' => $email,
                'disabled' => ($eventInvitation->getApplicationUser() != null),
                'constraints' => ($eventInvitation->getApplicationUser() == null ? new EmailNotBelongToAccountUser() : null)
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventInvitation::class
        ));
    }
}