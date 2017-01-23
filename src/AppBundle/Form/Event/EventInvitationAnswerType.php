<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/11/2016
 * Time: 13:50
 */

namespace AppBundle\Form\Event;


use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Utils\enum\EventInvitationAnswer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInvitationAnswerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var FormInterface $form */
                $form = $formEvent->getForm();
                /** @var EventInvitation $eventInvitation */
                $eventInvitation = $formEvent->getData();

                $event = $eventInvitation->getEvent();

                // Todo: décommenter
                // Désactivation temporaire pour simplification
                //if ($event->getStatus() == EventStatus::IN_CREATION || $event->getStatus() == EventStatus::IN_ORGANIZATION || $event->getStatus() == EventStatus::AWAITING_VALIDATION) {
                if (false) {
                    $form->add('answer', ChoiceType::class, array(
                        'required' => true,
                        'expanded' => true,
                        'multiple' => false,
                        'choices' => array(
                            EventInvitationAnswer::INTERESTED => EventInvitationAnswer::INTERESTED,
                            EventInvitationAnswer::NOT_INTERESTED => EventInvitationAnswer::NOT_INTERESTED
                        ),
                        'choice_attr' => function ($val, $key, $index) {
                            if ($val == EventInvitationAnswer::INTERESTED) {
                                $data_class = 'btn-answer-true zmdi zmdi-check';
                            } else {
                                $data_class = 'btn-answer-false zmdi zmdi-close';
                            }
                            return ['dataclass' => $data_class];
                        },
                        'choice_label' => false
                    ));
                    //} elseif ($event->getStatus() == EventStatus::VALIDATED) {
                } elseif (true) {
                    $form->add('answer', ChoiceType::class, array(
                        'required' => true,
                        'expanded' => true,
                        'multiple' => false,
                        'choices' => array(
                            EventInvitationAnswer::YES => EventInvitationAnswer::YES,
                            EventInvitationAnswer::DONT_KNOW => EventInvitationAnswer::DONT_KNOW,
                            EventInvitationAnswer::NO => EventInvitationAnswer::NO
                        ),
                        'choice_attr' => function ($val, $key, $index) {
                            if ($val == EventInvitationAnswer::YES) {
                                $data_class = 'btn-answer-true zmdi zmdi-check';
                            } elseif ($val == EventInvitationAnswer::NO) {
                                $data_class = 'btn-answer-false zmdi zmdi-close';
                            } else {
                                $data_class = 'btn-answer-maybe';
                            }
                            return ['dataclass' => $data_class];
                        },
                        'choice_label' => function ($val, $key, $index) {
                            if ($val == EventInvitationAnswer::YES) {
                                return false;
                            } elseif ($val == EventInvitationAnswer::NO) {
                                return false;
                            } else {
                                return "?";
                            }
                        }
                    ));
                }
            });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventInvitation::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "event_invitation_anwser";
    }
}