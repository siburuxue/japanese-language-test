<?php

namespace App\EventListener;

use App\Logger\SessionRequestProcessor;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    public function __construct(
        private SessionRequestProcessor $processor,
    ) {}

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->add([
            'global-token' => $this->processor->getToken()
        ]);
        $event->setResponse($response);
    }
}