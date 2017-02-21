<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 09/02/2017
 * Time: 16:20
 */

namespace AppBundle\Form\Event;

use AppBundle\Entity\Event\EventOpeningHours;
use AppBundle\Form\Type\DayOfWeekType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


class EventOpeningHoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dayOfWeek', DayOfWeekType::class, array(
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add('timeOpen', TimeType::class, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => new NotBlank()
            ))
            ->add('timeClosed', TimeType::class, array(
                'required' => true,
                'widget' => 'single_text',
                'constraints' => new NotBlank()
            ));
    }

    public function getBlockPrefix()
    {
        return 'event_openinghours';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventOpeningHours::class
        ));
    }
}