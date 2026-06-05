<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class VerificationCodeNotFoundException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.not_found';

    public static function forEmail(string $email): self
    {
        return new self(
            \sprintf('No active verification code found for "%s".', $email),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
