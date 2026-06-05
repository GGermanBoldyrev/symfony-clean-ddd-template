<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidEmailException;
use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class Email extends StringValueObject
{
    private const int MAX_LENGTH = 320;

    public static function fromString(string $value): self
    {
        $normalised = mb_strtolower(trim($value));

        if (!filter_var($normalised, \FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalidFormat($value);
        }

        if (mb_strlen($normalised) > self::MAX_LENGTH) {
            throw InvalidEmailException::tooLong($value);
        }

        return new self($normalised);
    }
}
