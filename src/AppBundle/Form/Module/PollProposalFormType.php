<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/07/2016
 * Time: 10:15
 */

namespace AppBundle\Form\Module;

use AppBundle\Entity\Module\PollProposal;
use AppBundle\Entity\Module\PollProposalElement;
use AppBundle\Utils\enum\PollElementType;
use Burgov\Bundle\KeyValueFormBundle\Form\Type\KeyValueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollProposalFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("strPPElts", KeyValueType::class, array(
                'value_type' => TextType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'delete_empty' => true,
                'prototype_name' => '__str_ppe__',
                'attr' => array(
                    'data-type' => 'string'
                )
            ))
            ->add("intPPElts", KeyValueType::class, array(
                'value_type' => IntegerType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'delete_empty' => true,
                'prototype_name' => '__int_ppe__',
                'attr' => array(
                    'data-type' => 'integer'
                )
            ))
            ->add("datetimePPElts", KeyValueType::class, array(
                'value_type' => DateTimeType::class,
                'required' => false,
                'mapped' => false,
                'by_reference' => false,
                'delete_empty' => true,
                'prototype_name' => '__datetime_ppe__',
                'attr' => array(
                    'data-type' => 'datetime'
                ),
                'value_options' => array(
                    'html5' => false,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'date_format' => 'dd/MM/yyyy'
                )
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                $form = $formEvent->getForm();
                /** @var PollProposal $pollProposal */
                $pollProposal = $formEvent->getData();
                $strPPE = array();
                $intPPE = array();
                $datetimePPE = array();
                /** @var PollProposalElement $ppElt */
                foreach ($pollProposal->getPollProposalElements() as $ppElt) {
                    if ($ppElt->getType() == PollElementType::STRING) {
                        $strPPE[$ppElt->getName()] = $ppElt->getValString();
                    } elseif ($ppElt->getType() == PollElementType::INTEGER) {
                        $intPPE[$ppElt->getName()] = $ppElt->getValInteger();
                    } elseif ($ppElt->getType() == PollElementType::DATE_TIME) {
                        $datetimePPE[$ppElt->getName()] = $ppElt->getValDatetime();
                    }
                }
                $form->get('strPPElts')->setData($strPPE);
                $form->get('intPPElts')->setData($intPPE);
                $form->get('datetimePPElts')->setData($datetimePPE);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollProposal::class
        ));
    }
}