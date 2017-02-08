<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 15/06/2016
 * Time: 18:05
 */

namespace AppBundle\Form\Event;

use AppBundle\Entity\Event\EventCoordinates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

class EventCoordinatesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("website", UrlType::class, array(
                'required' => false,
                'constraints' => new Url()
            ))
            ->add("email", EmailType::class, array(
                'required' => false,))
            ->add("phoneNumber", TextType::class, array(
                'required' => false,))
            ->add("mobileNumber", TextType::class, array(
                'required' => false,))
            ->add("faxNumber", TextType::class, array(
                'required' => false,))
            ->add("facebookURL", UrlType::class, array(
                'required' => false,
                'constraints' => new Url()
            ))
            ->add("googlePlusURL", UrlType::class, array(
                'required' => false,
                'constraints' => new Url()
            ))
            ->add("twitterURL", UrlType::class, array(
                'required' => false,
                'constraints' => new Url()
            ))
            ->add("instagramURL", UrlType::class, array(
                'required' => false,
                'constraints' => new Url()
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventCoordinates::class
        ));
    }
}