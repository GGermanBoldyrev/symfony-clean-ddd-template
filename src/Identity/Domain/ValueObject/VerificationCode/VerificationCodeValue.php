<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidVerificationCodeValueException;
use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class VerificationCodeValue extends StringValueObject
{
    private const int LENGTH = 6;

    public static function fromString(string $value): self
    {
        if (!preg_match('/^\d{' . self::LENGTH . '}$/', $value)) {
            throw InvalidVerificationCodeValueException::invalidFormat($value);
        }

        return new self($value);
    }

    public function matches(self $submitted): bool
    {
        return hash_equals($this->value, $submitted->value);
    }
}
