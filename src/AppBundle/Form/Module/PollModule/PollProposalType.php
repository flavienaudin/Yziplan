<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form\Module\PollModule;

use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Validator\Constraints\IntValuesInArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

class PollProposalType extends AbstractType
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
                ))
                ->add('valInteger', IntegerType::class, array(
                    'required' => true,
                    'constraints' => new NotBlank()
                ))
                ->add('valString', TextType::class, array(
                    'required' => true,
                    'attr' => array(
                        'class' => 'googlePlaceId_name',
                        'placeholder' => '' // Set blank placeholder to avoid google set its own
                    ),
                    'constraints' => new NotBlank()
                ))
                ->add('valGooglePlaceId', HiddenType::class, array(
                    'required' => false,
                    'attr' => array(
                        'class' => 'googlePlaceId_value'
                    )
                ))
                ->add('pictureFile', VichImageType::class, array(
                    'required' => false,
                    'label_attr' => array(
                        'class' => 'sr-only'
                    )
                ))
                ->add('valText', TextareaType::class, array())
                ->add('valString', TextType::class, array(
                    'required' => true,
                    'constraints' => new NotBlank()
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