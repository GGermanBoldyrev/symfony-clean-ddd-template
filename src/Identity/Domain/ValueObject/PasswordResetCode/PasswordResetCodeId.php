<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Identity\Domain\Exception\PasswordResetCode\InvalidPasswordResetCodeIdException;
use App\Shared\Domain\ValueObject\UuidValueObject;

final readonly class PasswordResetCodeId extends UuidValueObject
{
    public static function fromString(string $value): self
    {
        self::assertValid(
            $value,
            static fn () => throw InvalidPasswordResetCodeIdException::invalidFormat($value),
        );

        return new self($value);
    }
}
