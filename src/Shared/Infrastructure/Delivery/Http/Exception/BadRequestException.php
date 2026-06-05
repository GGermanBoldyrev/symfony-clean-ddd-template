<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Exception;

use RuntimeException;

final class BadRequestException extends RuntimeException
{
    public static function emptyBody(): self
    {
        return new self('Request body must not be empty.');
    }

    public static function invalidJson(): self
    {
        return new self('Request body contains invalid JSON.');
    }

    public static function missingField(string $field): self
    {
        return new self(\sprintf('Required field "%s" is missing.', $field));
    }

    public static function wrongType(string $field, string $expected): self
    {
        return new self(
            \sprintf('Field "%s" must be of type %s.', $field, $expected),
        );
    }
}
