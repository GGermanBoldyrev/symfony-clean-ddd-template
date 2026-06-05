<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidVerificationCodeIdException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.invalid_id';

    public static function invalidFormat(string $value): self
    {
        return new self(
            \sprintf('"%s" is not a valid UUID and cannot be used as VerificationCodeId.', $value),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
