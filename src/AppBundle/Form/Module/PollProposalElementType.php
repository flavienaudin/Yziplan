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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                $form->add('valDatetime', DateTimeType::class, array(
                    'required' => true,
                    'label' => $pollProposalElement->getPollElement()->getName(),
                    'html5' => false,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'date_format' => 'dd/MM/yyyy'
                ));
            } elseif ($pollProposalElement->getPollElement()->getType() == PollElementType::INTEGER) {
                $form->add('valInteger', IntegerType::class, array(
                    'required' => true,
                    'label' => $pollProposalElement->getPollElement()->getName()
                ));
            } else {
                $form->add('valString', TextType::class, array(
                    'required' => true,
                    'label' => $pollProposalElement->getPollElement()->getName()
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