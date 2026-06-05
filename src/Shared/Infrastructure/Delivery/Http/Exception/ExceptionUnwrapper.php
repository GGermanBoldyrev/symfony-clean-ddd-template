<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Exception;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

final class ExceptionUnwrapper
{
    public static function unwrap(Throwable $exception): Throwable
    {
        if ($exception instanceof HandlerFailedException) {
            return $exception->getPrevious() ?? $exception;
        }

        return $exception;
    }
}
