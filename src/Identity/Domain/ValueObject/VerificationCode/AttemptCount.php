<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidAttemptCountException;
use App\Shared\Domain\ValueObject\IntValueObject;

final readonly class AttemptCount extends IntValueObject
{
    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromInt(int $value): self
    {
        if ($value < 0) {
            throw InvalidAttemptCountException::negative($value);
        }

        return new self($value);
    }

    public function increment(): self
    {
        return new self($this->value + 1);
    }
}
