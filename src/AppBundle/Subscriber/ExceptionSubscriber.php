<?php

namespace AppBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements  EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ KernelEvents::EXCEPTION => 'onKernelException' ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->setResponse(new JsonResponse(['error' => $event->getException()->getMessage()]));
    }
}
