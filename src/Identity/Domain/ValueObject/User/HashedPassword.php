<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class HashedPassword extends StringValueObject
{
    /** @var list<non-empty-string> */
    private const array BCRYPT_PREFIXES = ['$2y$', '$2b$', '$2a$'];

    public static function fromHash(string $hash): self
    {
        if (!self::isBcrypt($hash)) {
            throw InvalidPasswordException::invalidHash();
        }

        return new self($hash);
    }

    public static function fromRawHash(string $hash): self
    {
        return new self($hash);
    }

    private static function isBcrypt(string $value): bool
    {
        return array_any(self::BCRYPT_PREFIXES, static fn ($prefix) => str_starts_with($value, $prefix));
    }
}
