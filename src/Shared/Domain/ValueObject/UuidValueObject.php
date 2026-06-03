<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Uuid\UuidGenerator;
use Stringable;

/**
 * Base for UUID-backed aggregate-root identifiers.
 */
abstract readonly class UuidValueObject implements Stringable
{
    protected function __construct(
        protected string $value,
    ) {}

    /**
     * Generates a new time-ordered UUID v7 for this identity type.
     */
    public static function generate(): static
    {
        return new static(UuidGenerator::generate());
    }

    final public function toString(): string
    {
        return $this->value;
    }

    final public function __toString(): string
    {
        return $this->value;
    }

    final public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Validates UUID format and invokes the callback when invalid.
     *
     * @param callable(): never $onInvalid
     */
    final protected static function assertValid(string $value, callable $onInvalid): void
    {
        if (!UuidGenerator::isValid($value)) {
            $onInvalid();
        }
    }
}
