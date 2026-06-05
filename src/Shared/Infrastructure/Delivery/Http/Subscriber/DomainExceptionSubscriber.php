<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;
use App\Identity\Domain\Exception\User\InvalidEmailException;
use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\Exception\VerificationCode\CodeExpiredException;
use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class DomainExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Only handle API routes.
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $response = match (true) {
            // HTTP layer — malformed request body
            $exception instanceof BadRequestException => ApiResponse::error(HttpErrorCode::BAD_REQUEST, $exception->getMessage()),

            // Domain validation — invalid input format
            $exception instanceof InvalidEmailException,
            $exception instanceof InvalidPasswordException => ApiResponse::error(HttpErrorCode::UNPROCESSABLE_ENTITY, $exception->getMessage()),

            // Domain policy — data policy not accepted
            $exception instanceof DataPolicyNotAcceptedException => ApiResponse::error(HttpErrorCode::UNPROCESSABLE_ENTITY, $exception->getMessage()),

            // Authentication failures
            $exception instanceof AuthenticationException => ApiResponse::error(HttpErrorCode::UNAUTHORIZED, $exception->getMessageKey()),

            // Conflict — resource already exists / already in desired state
            $exception instanceof UserAlreadyVerifiedException => ApiResponse::error(HttpErrorCode::CONFLICT, $exception->getMessage()),

            // Not found
            $exception instanceof UserNotFoundException,
            $exception instanceof VerificationCodeNotFoundException => ApiResponse::error(HttpErrorCode::NOT_FOUND, $exception->getMessage()),

            // Verification code errors
            $exception instanceof CodeExpiredException => ApiResponse::error(HttpErrorCode::UNPROCESSABLE_ENTITY, $exception->getMessage()),

            $exception instanceof InvalidCodeException => ApiResponse::error(HttpErrorCode::UNPROCESSABLE_ENTITY, $exception->getMessage()),

            $exception instanceof MaxAttemptsExceededException => ApiResponse::error(HttpErrorCode::TOO_MANY_REQUESTS, $exception->getMessage()),

            // Resend cooldown
            $exception instanceof ResendCooldownException => ApiResponse::error(HttpErrorCode::TOO_MANY_REQUESTS, $exception->getMessage()),

            default => null,
        };

        if ($response !== null) {
            $event->setResponse($response);
        }
    }
}
