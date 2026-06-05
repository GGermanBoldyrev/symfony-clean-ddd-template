<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Throwable;

/**
 * Marker for DomainExceptionSubscriber.
 */
interface DomainExceptionInterface extends Throwable
{
    public function getErrorCode(): string;
}
