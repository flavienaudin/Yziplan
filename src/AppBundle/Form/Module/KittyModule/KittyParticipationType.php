<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 21/04/2017
 * Time: 10:56
 */

namespace AppBundle\Form\Module\KittyModule;


use AppBundle\Entity\Module\KittyParticipation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KittyParticipationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, array(
                'required' => false
            ))
            ->add('message', TextareaType::class, array(
                'required' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => KittyParticipation::class
        ));
    }
}