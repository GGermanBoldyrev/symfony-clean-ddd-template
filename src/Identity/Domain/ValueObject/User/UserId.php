<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidUserIdException;
use App\Shared\Domain\ValueObject\UuidValueObject;

final readonly class UserId extends UuidValueObject
{
    public static function fromString(string $value): self
    {
        self::assertValid(
            $value,
            static fn () => throw InvalidUserIdException::invalidFormat($value),
        );

        return new self($value);
    }
}
