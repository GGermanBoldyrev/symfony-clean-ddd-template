<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\PasswordResetCode;

use App\Identity\Domain\Exception\PasswordResetCode\InvalidExpiresAtException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class ExpiresAt extends DateTimeValueObject
{
    private function __construct(DateTimeImmutable $value)
    {
        parent::__construct($value);
    }

    public static function from(DateTimeImmutable $value, ?DateTimeImmutable $now = null): self
    {
        $now ??= new DateTimeImmutable();

        if ($value <= $now) {
            throw InvalidExpiresAtException::notInFuture();
        }

        return new self($value);
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $now >= $this->value;
    }
}
