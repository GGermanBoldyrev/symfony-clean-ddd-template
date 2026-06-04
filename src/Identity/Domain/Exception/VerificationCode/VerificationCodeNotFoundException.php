<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class VerificationCodeNotFoundException extends DomainException
{
    public static function forEmail(string $email): self
    {
        return new self(
            sprintf('No active verification code found for "%s".', $email),
        );
    }
}
