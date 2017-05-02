<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/06/2016
 * Time: 15:20
 */

namespace AppBundle\Form\Module;


use AppBundle\Entity\Event\Module;
use AppBundle\Form\Module\PollModule\PollModuleFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, array(
                'required' => false
            ))
            ->add("description", TextareaType::class, array(
                'required' => false
            ))
            ->add("responseDeadline", DateTimeType::class, array(
                "required" => false,
                'html5' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy'
            ))
            ->add('guestsCanInvite', CheckboxType::class, array(
                    'required' => false
                )
            )
            ->add('invitationOnly', ChoiceType::class, array(
                    "expanded" => true,
                    "multiple" => false,
                    'required' => false,
                    'empty_data' => null,
                    'placeholder' => 'module.form.choice.label.inherit',
                    'choices' => array(
                        'yes' => true,
                        'no' => false),
                    'choice_label' => function ($value, $key, $index) {
                        return 'module.form.choice.label.' . $key;
                    }
                )
            );
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) {
                /** @var Module $data */
                $data = $formEvent->getData();
                if ($data->getPollModule() != null) {
                    /** @var FormInterface $form */
                    $form = $formEvent->getForm();
                    $form->add('pollModule', PollModuleFormType::class, array(
                        'required' => false
                    ));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Module::class
        ));
    }
}