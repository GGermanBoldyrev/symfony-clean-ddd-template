<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Identity\Domain\Exception\PasswordResetCode\PasswordResetCodeExpiredException;
use DateTimeImmutable;

final readonly class ExpiresAt
{
    private function __construct(
        public readonly DateTimeImmutable $value,
    ) {
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function assertNotExpired(DateTimeImmutable $now): void
    {
        if ($now >= $this->value) {
            throw PasswordResetCodeExpiredException::expired();
        }
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->value;
    }
}
