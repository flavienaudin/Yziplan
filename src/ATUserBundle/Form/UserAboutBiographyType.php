<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 09/06/2016
 * Time: 10:41
 */

namespace ATUserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class UserAboutBiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("biography", TextareaType::class, array(
                "required" => false,
            ));
    }

    public function getBlockPrefix()
    {
        return 'userabout_biography';
    }
}