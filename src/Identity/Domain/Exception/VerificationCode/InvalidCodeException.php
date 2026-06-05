<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidCodeException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.invalid';

    public static function mismatch(): self
    {
        return new self('The submitted verification code is incorrect.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
