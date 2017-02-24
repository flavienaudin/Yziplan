<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 23/02/2017
 * Time: 18:15
 */

namespace AppBundle\Form\Core;


use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuggestionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("titre", TextType::class, array(
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add("description", TextareaType::class, array(
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add("name", TextType::class, array(
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank()
            ))
            ->add("mail", EmailType::class, array(
                'mapped' => false,
                'required' => true,
                'constraints' => new Email()
            ))
            ->add("resolution", HiddenType::class, array(
                'mapped' => false
            ))
            ->add("os", HiddenType::class, array(
                'mapped' => false
            ))
            ->add("navigateur", HiddenType::class, array(
                'mapped' => false
            ))
            ->add("userAgent", HiddenType::class, array(
                'mapped' => false
            ))
            ->add("pageURL", HiddenType::class, array(
                'mapped' => false
            ))
            ->add('suggestionCaptcha', CaptchaType::class, array(
                'captchaConfig' => 'SuggestionCaptcha',
                'required' => true,
                'constraints' => array(
                    new ValidCaptcha(),
                    new NotBlank())

            ));
    }
}