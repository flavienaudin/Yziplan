<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 20/02/2017
 * Time: 16:49
 */

namespace AppBundle\Form\Event;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class EventTemplateSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("activateTemplate", CheckboxType::class, array(
            'required' => false
        ));
    }
}