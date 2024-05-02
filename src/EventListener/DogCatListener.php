<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DogCatListener
{
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->setContent(str_replace('Dog', 'Cat', $response->getContent()));
        $response->setContent(str_replace('Pates', 'Cats', $response->getContent()));
    }
}
