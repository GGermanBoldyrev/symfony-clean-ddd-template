<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use DateTimeImmutable;

final readonly class ResendAfter
{
    private function __construct(
        public readonly DateTimeImmutable $value,
    ) {
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    /**
     * Returns true when enough time has passed and a new code may be sent.
     */
    public function isAllowed(DateTimeImmutable $now): bool
    {
        return $now >= $this->value;
    }
}
