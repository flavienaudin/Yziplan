<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 15/03/2017
 * Time: 18:15
 */

namespace AppBundle\Form\ActivityDirectory;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class SearchActivityType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, array(
                'class' => 'AppBundle:ActivityDirectory\ActivityType',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.keyName', 'ASC');
                },
                'choice_label' => 'keyName',
                'choice_value' => 'id',
                'expanded' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ))
            ->add("place", TextType::class, array(
                'mapped' => false,
                'required' => false
            ));
    }
}