<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Identity\Domain\Exception\PasswordResetCode\InvalidPasswordResetCodeValueException;
use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class PasswordResetCodeValue extends StringValueObject
{
    private const int LENGTH = 6;

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if (preg_match('/^\d{' . self::LENGTH . '}$/', $trimmed) !== 1) {
            throw InvalidPasswordResetCodeValueException::invalidFormat($trimmed);
        }

        return new self($trimmed);
    }

    // Crypto-safe comparison
    public function matches(self $submitted): bool
    {
        return hash_equals($this->value, $submitted->value);
    }
}
