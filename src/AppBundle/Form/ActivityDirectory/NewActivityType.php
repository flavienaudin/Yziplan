<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 15/03/2017
 * Time: 18:15
 */

namespace AppBundle\Form\ActivityDirectory;

use AppBundle\Entity\ActivityDirectory\Activity;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NewActivityType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', EntityType::class, array(
                'class' => 'AppBundle:Event\Event',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('e');
                    $qb2 = $er->createQueryBuilder('e2');
                    return $qb->where('e.askDirectory = 1')
                        ->andwhere(
                            $qb->expr()->notIn(
                                'e',
                                $qb2->select('e2')
                                    ->join(Activity::class, 'a',  'WITH', 'a.event = e2')
                                    ->getDQL()
                            ));
                },
                'choice_label' => 'name',
                'choice_value' => 'id',
                'expanded' => false,
                'multiple' => false,
                'mapped' => false,
                'required' => false
            ))
            ->add('activityTypes', EntityType::class, array(
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
            ));;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Activity::class
        ));
    }
}