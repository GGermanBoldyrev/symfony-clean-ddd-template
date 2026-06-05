<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Stringable;

/**
 * Base for any Value Object whose entire state is a single immutable string.
 */
abstract readonly class StringValueObject implements Stringable
{
    protected function __construct(
        protected string $value,
    ) {
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
}
