<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Infrastructure\Delivery\Http\Exception\ExceptionUnwrapper;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class DomainExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = ExceptionUnwrapper::unwrap($event->getThrowable());

        // Only handle API routes.
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        // Only DomainExceptionInterface implementations
        if (!$exception instanceof DomainExceptionInterface) {
            return;
        }

        $statusCode = match (true) {
            // Not Found - resource not found
            $exception instanceof UserNotFoundException,
            $exception instanceof VerificationCodeNotFoundException => HttpErrorCode::NOT_FOUND,

            // Too many requests - rate limiters
            $exception instanceof MaxAttemptsExceededException,
            $exception instanceof ResendCooldownException => HttpErrorCode::TOO_MANY_REQUESTS,

            // All other domain validation / logic exceptions
            default => HttpErrorCode::UNPROCESSABLE_ENTITY,
        };

        $event->setResponse(ApiResponse::error($statusCode, $exception->getErrorCode(), $exception->getMessage()));
    }
}
