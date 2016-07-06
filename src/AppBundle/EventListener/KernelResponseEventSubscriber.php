<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/07/2016
 * Time: 11:39
 */

namespace AppBundle\EventListener;

use AppBundle\Utils\FlashBagTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\Translator;

class KernelResponseEventSubscriber implements EventSubscriberInterface
{

    /** @var Translator */
    private $translator;

    /** @var FlashBag */
    private $flashBag;

    public function __construct(Translator $translator, FlashBag $flashBag)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
    }


    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    public function onKernelREsponse(FilterResponseEvent $event)
    {
        if ($event->getResponse()->getStatusCode() == Response::HTTP_INTERNAL_SERVER_ERROR) {
            $request = $event->getRequest();
            if (empty($request->get('messages'))) {
                if ($request->isXmlHttpRequest()) {
                    $data['messages'][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("global.error.internal_server_error");
                    $event->setResponse(new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR));
                } else {
                    if ($request->hasSession()) {
                        $this->flashBag->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("global.error.internal_server_error"));
                    }
                }
            }
        }
    }
}