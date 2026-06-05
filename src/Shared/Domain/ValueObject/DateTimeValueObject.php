<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use DateTimeImmutable;

abstract readonly class DateTimeValueObject
{
    protected function __construct(
        protected DateTimeImmutable $value,
    ) {
    }

    final public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->value;
    }

    final public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
