<?php


declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class PlainPassword extends StringValueObject
{
    private const int MIN_LENGTH = 8;
    private const int MAX_LENGTH = 72;

    public static function fromString(string $value): self
    {
        $length = mb_strlen($value);

        if ($length < self::MIN_LENGTH) {
            throw InvalidPasswordException::tooShort(self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidPasswordException::tooLong(self::MAX_LENGTH);
        }

        return new self($value);
    }
}
