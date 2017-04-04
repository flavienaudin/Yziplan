<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 30/03/2017
 * Time: 12:44
 */

namespace AppBundle\EventListener\Form;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixUrlDomainListener implements EventSubscriberInterface
{
    private $urlDomain;

    /**
     * Constructor.
     *
     * @param string $urlDomain The URL scheme to add when there is none or null to not modify the data
     */
    public function __construct($urlDomain)
    {
        $this->urlDomain = $urlDomain;
    }
    public static function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => 'onSubmit');
    }

    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($this->urlDomain && $data && !preg_match('~^[\w+.-]+://~', $data)) {
            $event->setData($this->urlDomain.$data);
        }
    }
}