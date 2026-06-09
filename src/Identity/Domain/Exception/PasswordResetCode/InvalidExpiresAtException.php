<?php

namespace App\Identity\Domain\Exception\PasswordResetCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidExpiresAtException extends DomainException
{
    private const string ERROR_CODE = 'password_reset_code.invalid_expires_at';

    public static function notInFuture(): self
    {
        return new self('Expiration date must be in the future.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
