<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/02/2017
 * Time: 16:26
 */

namespace AppBundle\Form\Event;


use AppBundle\Form\Type\SelectionEventInvitationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SendMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("selection", SelectionEventInvitationType::class, array(
                'mapped' => false,
                'multiple' => true,
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add('message', TextareaType::class, array(
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank()
            ));
    }
}