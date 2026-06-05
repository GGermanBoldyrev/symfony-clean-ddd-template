<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use App\Shared\Infrastructure\Delivery\Http\Exception\ExceptionUnwrapper;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 2222)]
final class HttpLayerExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = ExceptionUnwrapper::unwrap($event->getThrowable());

        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $response = match (true) {
            $exception instanceof MethodNotAllowedHttpException => ApiResponse::error(
                HttpErrorCode::METHOD_NOT_ALLOWED,
                'request.method_not_allowed',
                $exception->getMessage(),
            ),

            $exception instanceof BadRequestException => ApiResponse::error(
                HttpErrorCode::BAD_REQUEST,
                'request.bad_request',
                $exception->getMessage(),
            ),

            $exception instanceof AuthenticationException,
            $exception instanceof AccessDeniedException,
            $exception instanceof AccessDeniedHttpException => ApiResponse::error(
                HttpErrorCode::UNAUTHORIZED,
                'auth.unauthorized',
                $exception->getMessage(),
            ),

            default => null,
        };

        if ($response !== null) {
            $event->setResponse($response);
        }
    }
}
