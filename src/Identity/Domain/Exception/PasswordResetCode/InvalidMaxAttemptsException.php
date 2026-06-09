<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\PasswordResetCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidMaxAttemptsException extends DomainException
{
    private const string ERROR_CODE = 'password_reset_code.invalid_max_attempts';

    public static function notPositive(int $value): self
    {
        return new self(
            \sprintf('Max attempts must be a positive integer, got %d.', $value),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
