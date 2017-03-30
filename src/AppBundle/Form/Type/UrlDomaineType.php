<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 30/03/2017
 * Time: 12:41
 */

namespace AppBundle\Form\Type;


use AppBundle\EventListener\Form\FixUrlDomainListener;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UrlDomaineType extends UrlType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['url_domain']) {
            $builder->addEventSubscriber(new FixUrlDomainListener($options['url_domain']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('url_domain');
        $resolver->setAllowedTypes('url_domain', array('string'));
    }
}