<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 02/05/2017
 * Time: 13:19
 */

namespace AppBundle\Form\Module\PollModule;


use AppBundle\Entity\Module\PollModule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("guestsCanAddProposal", CheckboxType::class, array(
                'required' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PollModule::class
        ));
    }
}