<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidVerificationCodeValueException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.invalid_value';

    public static function invalidFormat(string $value): self
    {
        return new self(
            \sprintf('"%s" is not a valid verification code. Expected 6 digits.', $value),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
