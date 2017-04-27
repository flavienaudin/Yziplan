<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 10/04/2017
 * Time: 16:34
 */

namespace AppBundle\Form\Type;


use AppBundle\Utils\enum\NotificationFrequencyEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationFrequencyType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                NotificationFrequencyEnum::NEVER => NotificationFrequencyEnum::NEVER,
                NotificationFrequencyEnum::EACH_NOTIFICATION => NotificationFrequencyEnum::EACH_NOTIFICATION
// TODO activer les options de rapports quotidiens/hebdomadaires quand la fonction d'envoi d'email le permettra
//            ,
//                NotificationFrequencyEnum::DAILY => NotificationFrequencyEnum::DAILY,
//                NotificationFrequencyEnum::WEEKLY => NotificationFrequencyEnum::WEEKLY
            )
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}