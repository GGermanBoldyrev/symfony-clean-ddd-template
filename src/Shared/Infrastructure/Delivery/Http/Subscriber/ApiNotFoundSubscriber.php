<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiNotFoundSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // События и их приоритет
            KernelEvents::REQUEST => ['onKernelRequest', 32],
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Если основной запрос, а не внутренний редирект
        if (!$event->isMainRequest()) {
            return;
        }

        // С запроса на главную - 404
        if ($event->getRequest()->getPathInfo() === '/') {
            $event->setResponse(ApiResponse::error(HttpErrorCode::NOT_FOUND));
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $event->setResponse(ApiResponse::error(HttpErrorCode::NOT_FOUND));
    }
}
