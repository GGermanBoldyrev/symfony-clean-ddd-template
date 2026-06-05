<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidMaxAttemptsException;
use App\Shared\Domain\ValueObject\IntValueObject;

final readonly class MaxAttempts extends IntValueObject
{
    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw InvalidMaxAttemptsException::notPositive($value);
        }

        return new self($value);
    }

    public function isExceeded(AttemptCount $attempts): bool
    {
        return $attempts->toInt() >= $this->value;
    }
}
