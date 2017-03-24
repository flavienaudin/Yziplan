<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/11/2016
 * Time: 10:37
 */

namespace AppBundle\Form\Module;


use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Utils\enum\PollElementType;
use AppBundle\Validator\Constraints\IntValuesInArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PollProposalWhenElementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', DateType::class, array(
            'required' => true,
            'input' => 'array',
            'html5' => false,
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'mapped' => false,
            'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], false)
        ))
            ->add('startTime', TimeType::class, array(
                'required' => false,
                'input' => 'array',
                'html5' => false,
                'widget' => 'single_text',
                'mapped' => false,
                'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
            ))
            ->add('endDate', DateType::class, array(
                'required' => false,
                'input' => 'array',
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'mapped' => false,
                'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], true)
            ))
            ->add('endTime', TimeType::class, array(
                'required' => false,
                'input' => 'array',
                'html5' => false,
                'widget' => 'single_text',
                'mapped' => false,
                'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposalElement::class
        ));
    }
}