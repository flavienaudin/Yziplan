<?php
/**
 * User: Patrick
 * Date: 27/03/2017
 * Time: 15:03
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Entity\Module\PollProposal;
use AppBundle\Validator\Constraints\IntValuesInArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollProposalWhenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var FormInterface $form */
            $form = $formEvent->getForm();
            /** @var PollProposal $pollProposal */
            $pollProposal = $formEvent->getData();

            $form->add('startDate', DateType::class, array(
                'required' => true,
                'input' => 'array',
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'mapped' => false,
                'data' => $pollProposal != null ? $pollProposal->getArrayFromDate() : null,
                'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], false)
            ))
                ->add('startTime', TimeType::class, array(
                    'required' => false,
                    'input' => 'array',
                    'html5' => false,
                    'widget' => 'single_text',
                    'mapped' => false,
                    'data' => $pollProposal != null ? $pollProposal->getArrayFromTime() : null,
                    'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
                ))
                ->add('endDate', DateType::class, array(
                    'required' => false,
                    'input' => 'array',
                    'html5' => false,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'mapped' => false,
                    'data' => $pollProposal != null ? $pollProposal->getArrayFromEndDate() : null,
                    'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], true)
                ))
                ->add('endTime', TimeType::class, array(
                    'required' => false,
                    'input' => 'array',
                    'html5' => false,
                    'widget' => 'single_text',
                    'mapped' => false,
                    'data' => $pollProposal != null ? $pollProposal->getArrayFromEndTime() : null,
                    'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
                ));

            if (($pollProposal != null) && !empty($pollProposal->getId())) {
                $form->add('id', HiddenType::class, array(
                    'disabled' => true
                ));
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}