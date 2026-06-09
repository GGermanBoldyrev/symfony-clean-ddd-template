<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\PasswordResetCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidResendAfterException extends DomainException
{
    private const string ERROR_CODE = 'password_reset_code.invalid_resend_after';

    public static function notInFuture(): self
    {
        return new self('Resend-after date must be in the future.');
    }

    public static function afterExpiration(): self
    {
        return new self('Resend-after date must be before the expiration date.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
