<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Identity\Domain\Exception\PasswordResetCode\PasswordResetMaxAttemptsExceededException;
use App\Shared\Domain\ValueObject\IntValueObject;

final readonly class AttemptCount extends IntValueObject
{
    private function __construct(
        public readonly int $value,
    ) {
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function increment(): self
    {
        return new self($this->value + 1);
    }

    public function assertNotExceeded(MaxAttempts $max): void
    {
        if ($this->value >= $max->value) {
            throw PasswordResetMaxAttemptsExceededException::exceeded();
        }
    }

    public function isExceeded(MaxAttempts $max): bool
    {
        return $this->value >= $max->value;
    }
}
