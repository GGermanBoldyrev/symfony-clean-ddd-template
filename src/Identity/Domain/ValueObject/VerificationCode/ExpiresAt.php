<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidExpiresAtException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class ExpiresAt extends DateTimeValueObject
{
    private function __construct(DateTimeImmutable $value)
    {
        parent::__construct($value);
    }

    public static function from(DateTimeImmutable $value): self
    {
        if ($value <= new DateTimeImmutable()) {
            throw InvalidExpiresAtException::notInFuture();
        }

        return new self($value);
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->value;
    }
}
