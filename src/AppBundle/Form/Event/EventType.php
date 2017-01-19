<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/06/2016
 * Time: 18:05
 */

namespace AppBundle\Form\Event;

use AppBundle\Entity\Event\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add("invitationOnly", CheckboxType::class, array(
                'required' => false
            ))
            ->add("guestsCanInvite", CheckboxType::class, array(
                'required' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class
        ));
    }
}