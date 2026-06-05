<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidAttemptCountException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.invalid_attempt_count';

    public static function negative(int $value): self
    {
        return new self(
            \sprintf('Attempt count cannot be negative, got %d.', $value),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
