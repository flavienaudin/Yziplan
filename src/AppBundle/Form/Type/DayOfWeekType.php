<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:17
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\DayOfWeek;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayOfWeekType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                DayOfWeek::MONDAY => DayOfWeek::MONDAY,
                DayOfWeek::TUESDAY => DayOfWeek::TUESDAY,
                DayOfWeek::WEDNESDAY => DayOfWeek::WEDNESDAY,
                DayOfWeek::THURSDAY => DayOfWeek::THURSDAY,
                DayOfWeek::FRIDAY => DayOfWeek::FRIDAY,
                DayOfWeek::SATURDAY => DayOfWeek::SATURDAY,
                DayOfWeek::SUNDAY => DayOfWeek::SUNDAY
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}