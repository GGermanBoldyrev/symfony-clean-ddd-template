<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Shared\Domain\ValueObject\IntValueObject;
use InvalidArgumentException;

final readonly class MaxAttempts extends IntValueObject
{
    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Max attempts must be at least 1.');
        }

        return new self($value);
    }
}
