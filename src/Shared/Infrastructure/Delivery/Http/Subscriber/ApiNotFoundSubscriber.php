<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 32)]
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 0)]
final class ApiNotFoundSubscriber
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->getPathInfo() === '/') {
            $event->setResponse(ApiResponse::error(HttpErrorCode::NOT_FOUND, 'route.not_found'));
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $event->setResponse(ApiResponse::error(HttpErrorCode::NOT_FOUND, 'route.not_found'));
    }
}
