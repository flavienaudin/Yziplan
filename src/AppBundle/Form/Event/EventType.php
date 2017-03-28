<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/06/2016
 * Time: 18:05
 */

namespace AppBundle\Form\Event;

use AppBundle\Entity\Event\Event;
use AppBundle\Entity\Event\EventOpeningHours;
use AppBundle\Form\Type\DayOfWeekType;
use AppBundle\Utils\enum\DayOfWeek;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, array(
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add("description", TextareaType::class, array(
                    'required' => false
                )
            )
            ->add("whereName", TextType::class, array(
                    'required' => false
                )
            )
            ->add("whereGooglePlaceId", HiddenType::class, array(
                    'required' => false
                )
            )
            ->add("when", DateTimeType::class, array(
                "required" => false,
                'html5' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy'
            ))
            /* TODO décommenter quand la date limite de réponse sera utilisée
            ->add("responseDeadline", DateTimeType::class, array(
                "required" => false,
                'html5' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy'
            ))*/
            /* TODO : champs désactivés pour simplifier l'interface
            ->add("invitationOnly", CheckboxType::class, array(
                'required' => false
            ))
            ->add("guestsCanInvite", CheckboxType::class, array(
                'required' => false
            ))*/
            ->add('coordinates', EventCoordinatesType::class, array(
                'required' => false,
                'label_attr' => array('class' => 'sr-only')
            ))
            ->add("activityProvider", CheckboxType::class, array(
                'required' => false
            ))
            ->add("template", CheckboxType::class, array(
                'required' => false
            ))
            ->add("askDirectory", CheckboxType::class, array(
                'required' => false
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                $form = $formEvent->getForm();
                /** @var Event $event*/
                $event = $formEvent->getData();
                if(count($event->getOpeningHours()) == 0 ){
                    $event->initializeWeekOpeningHours();
                }
                $form
                    ->add('openingHours', CollectionType::class, array(
                        'required' => false,
                        'label_attr' => array(
                            'class' => 'sr-only'
                        ),
                        'entry_type' => EventOpeningHoursType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'by_reference' => false
                    ));
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class
        ));
    }
}