<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use App\Shared\Infrastructure\Delivery\Http\Exception\ExceptionUnwrapper;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 20)]
final class HttpLayerExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = ExceptionUnwrapper::unwrap($event->getThrowable());

        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $response = match (true) {
            $exception instanceof BadRequestException => ApiResponse::error(
                HttpErrorCode::BAD_REQUEST,
                'request.bad_request',
                $exception->getMessage(),
            ),

            $exception instanceof AuthenticationException => ApiResponse::error(
                HttpErrorCode::UNAUTHORIZED,
                'auth.unauthorized',
                $exception->getMessageKey(),
            ),

            default => null,
        };

        if ($response !== null) {
            $event->setResponse($response);
        }
    }
}
