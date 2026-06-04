<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class InvalidPasswordException extends DomainException
{
    public static function tooShort(int $min): self
    {
        return new self(sprintf('Password must be at least %d characters long.', $min));
    }

    public static function tooLong(int $max): self
    {
        return new self(sprintf('Password must not exceed %d characters.', $max));
    }

    public static function invalidHash(): self
    {
        return new self('The provided string is not a valid bcrypt hash.');
    }
}

