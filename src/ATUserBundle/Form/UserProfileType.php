<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 03/02/2016
 * Time: 12:47
 */

namespace ATUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("pseudo", TextType::class, array(
                "label" => "Nom public",
                'required' => false
            ))
            // Suppression du mot de passe pour pouvoir modifier son profil (pour les compte ReseauSoc notamment)
            ->remove("current_password");
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix()
    {
        return 'user_user_profile';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}