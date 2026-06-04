<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class InvalidUserIdException extends DomainException
{
    public static function invalidFormat(string $value): self
    {
        return new self(sprintf('"%s" is not a valid UUID and cannot be used as UserId.', $value));
    }
}
