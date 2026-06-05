<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class InvalidEmailException extends DomainException
{
    private const string ERROR_CODE = 'user.invalid_email';

    public static function invalidFormat(string $value): self
    {
        return new self(sprintf('"%s" has an invalid e-mail format.', $value));
    }

    public static function tooLong(string $value): self
    {
        return new self(sprintf('"%s" exceeds the 320-character RFC 5321 §4.5.3 limit.', $value));
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
