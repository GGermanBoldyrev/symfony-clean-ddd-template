<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;

final readonly class AccessToken extends StringValueObject
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
