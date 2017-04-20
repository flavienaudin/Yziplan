<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 20/04/2017
 * Time: 14:23
 */

namespace AppBundle\Form\Module\KittyModule;


use AppBundle\Entity\Module\KittyModule;
use AppBundle\Utils\enum\KittyObjectiveTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KittyModuleType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objectiveAmount', NumberType::class, array(
                'required' => false
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                $form = $formEvent->getForm();
                /** @var KittyModule $kittyModule */
                $kittyModule = $formEvent->getData();

                $form->add('indicativeObjective', CheckboxType::class, array(
                    'mapped' => false,
                    'required' => false,
                    'data' => ($kittyModule->getObjectiveType() === KittyObjectiveTypeEnum::INDICATIVE)
                ));
            });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => KittyModule::class
        ));
    }
}