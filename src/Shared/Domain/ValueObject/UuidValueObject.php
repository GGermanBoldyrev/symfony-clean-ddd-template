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
    /** @param non-empty-string $value */
    protected function __construct(
        protected string $value,
    ) {
    }

    /**
     * Generates a new time-ordered UUID v7 for this identity type.
     */
    public static function generate(): static
    {
        /** @var class-string<static> $class */
        $class = static::class;

        return new $class(UuidGenerator::generate());
    }

    /**
     * @return non-empty-string
     */
    final public function toString(): string
    {
        return $this->value;
    }

    /**
     * @return non-empty-string
     */
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
     * @psalm-assert non-empty-string $value
     *
     * @phpstan-assert non-empty-string $value
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
