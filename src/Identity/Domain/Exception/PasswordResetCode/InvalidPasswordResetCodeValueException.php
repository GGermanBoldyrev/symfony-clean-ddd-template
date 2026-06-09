<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\PasswordResetCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidPasswordResetCodeValueException extends DomainException
{
    private const string ERROR_CODE = 'password_reset_code.invalid_value';

    public static function invalidFormat(string $value): self
    {
        return new self(
            \sprintf('"%s" is not a valid password reset code. Expected 6 digits.', $value),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
