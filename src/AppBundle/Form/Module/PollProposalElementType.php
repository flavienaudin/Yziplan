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

class PollProposalElementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
            /** @var FormInterface $form */
            $form = $formEvent->getForm();
            /** @var PollProposalElement $pollProposalElement */
            $pollProposalElement = $formEvent->getData();

            if ($pollProposalElement->getPollElement()->getType() == PollElementType::DATETIME) {
                $form->add('startDate', DateType::class, array(
                    'required' => true,
                    'input' => 'array',
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'html5' => false,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'mapped' => false,
                    'data' => $pollProposalElement->getArrayFromDate(),
                    'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], false)
                ))
                    ->add('startTime', TimeType::class, array(
                        'required' => false,
                        'input' => 'array',
                        'label' => $pollProposalElement->getPollElement()->getName(),
                        'html5' => false,
                        'widget' => 'single_text',
                        'mapped' => false,
                        'data' => $pollProposalElement->getArrayFromTime(),
                        'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
                    ));
            } elseif ($pollProposalElement->getPollElement()->getType() == PollElementType::END_DATETIME) {
                $form->add('endDate', DateType::class, array(
                    'required' => false,
                    'input' => 'array',
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'html5' => false,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'mapped' => false,
                    'data' => $pollProposalElement->getArrayFromEndDate(),
                    'constraints' => new IntValuesInArray('dd/MM/yyyy', ["year", "month", "day"], true)
                ))
                    ->add('endTime', TimeType::class, array(
                        'required' => false,
                        'input' => 'array',
                        'label' => $pollProposalElement->getPollElement()->getName(),
                        'html5' => false,
                        'widget' => 'single_text',
                        'mapped' => false,
                        'data' => $pollProposalElement->getArrayFromEndTime(),
                        'constraints' => new IntValuesInArray('hh:mm', ["hour", "minute"], true)
                    ));
            } elseif ($pollProposalElement->getPollElement()->getType() == PollElementType::INTEGER) {
                $form->add('valInteger', IntegerType::class, array(
                    'required' => true,
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'constraints' => new NotBlank()
                ));
            } elseif ($pollProposalElement->getPollElement()->getType() == PollElementType::GOOGLE_PLACE_ID) {
                $form->add('valString', TextType::class, array(
                    'required' => true,
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'attr' => array(
                        'class' => 'googlePlaceId_name',
                        'placeholder' => '' // Set blank placeholder to avoid google set its own
                    ),
                    'constraints' => new NotBlank()
                ));
                $form->add('valGooglePlaceId', HiddenType::class, array(
                    'required' => false,
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'attr' => array(
                        'class' => 'googlePlaceId_value'
                    )
                ));
            } elseif ($pollProposalElement->getPollElement()->getType() == PollElementType::RICHTEXT) {
                $form->add('valString', TextareaType::class, array(
                    'required' => false,
                ));
            }
            else {
                $required = false;
                if($pollProposalElement->getPollElement()->getType() == PollElementType::STRING){
                    $required = true;
                }
                $form->add('valString', TextType::class, array(
                    'required' => $required,
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'constraints' => new NotBlank()
                ));
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposalElement::class
        ));
    }
}