<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/07/2016
 * Time: 11:39
 */

namespace AppBundle\EventListener;

use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class KernelEventsSubscriber implements EventSubscriberInterface
{

    /** @var Translator */
    private $translator;

    /** @var FlashBag */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    public function __construct(Translator $translator, FlashBag $flashBag, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }


    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }

    public function onKernelREsponse(FilterResponseEvent $event)
    {
        if ($event->getResponse()->getStatusCode() == Response::HTTP_INTERNAL_SERVER_ERROR) {
            $request = $event->getRequest();
            if (empty($request->get('messages'))) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("global.error.internal_server_error");
                    $event->setResponse(new AppJsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR));
                } else {
                    if ($request->hasSession()) {
                        $this->flashBag->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("global.error.internal_server_error"));
                    }
                }
            }
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof AccessDeniedHttpException) {
            $request = $event->getRequest();
            if (empty($request->get('messages'))) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("global.exception.access_denied_http");
                    $event->setResponse(new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED));
                } else {
                    if ($request->hasSession()) {
                        $this->flashBag->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("global.exception.access_denied_http"));
                    }
                    $event->setResponse(new RedirectResponse($this->router->generate('home')));
                }
            }
        } elseif ($exception instanceof NotFoundHttpException || $exception instanceof MethodNotAllowedHttpException) {
            $request = $event->getRequest();
            if (empty($request->get('messages'))) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->translator->trans("global.exception.method_not_allowed_http");
                    $event->setResponse(new AppJsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR));
                } else {
                    if ($request->hasSession()) {
                        $this->flashBag->add(FlashBagTypes::ERROR_TYPE, $this->translator->trans("global.exception.method_not_allowed_http"));
                    }
                    $event->setResponse(new RedirectResponse($this->router->generate('home')));
                }
            }
        }
    }
}