<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidAttemptCountException extends DomainException
{
    public static function negative(int $value): self
    {
        return new self(
            sprintf('Attempt count cannot be negative, got %d.', $value),
        );
    }
}
