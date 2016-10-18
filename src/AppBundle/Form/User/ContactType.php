<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 17/10/2016
 * Time: 16:09
 */

namespace AppBundle\Form\User;


use AppBundle\Entity\User\Contact;
use AppBundle\Entity\User\ContactEmail;
use AppBundle\Form\Type\GenderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\LessThan;

class ContactType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, array(
                'required' => true
            ))
            ->add('lastName', TextType::class, array(
                'required' => false
            ))
            ->add('gender', GenderType::class, array(
                'required' => false
            ))
            ->add('birthday', BirthdayType::class, array(
                'required' => false,
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy'
            ))
            ->add('nationality', TextType::class, array(
                'required' => false
            ))/*->add('contactEmails', CollectionType::class, array(
                'entry_type' => ContactEmailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
            ))*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Contact::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_user_contact';
    }
}