<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract readonly class IntValueObject
{
    protected function __construct(
        protected int $value,
    ) {
    }

    final public function toInt(): int
    {
        return $this->value;
    }

    final public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
