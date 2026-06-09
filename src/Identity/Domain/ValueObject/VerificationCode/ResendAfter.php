<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidResendAfterException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class ResendAfter extends DateTimeValueObject
{
    private function __construct(DateTimeImmutable $value)
    {
        parent::__construct($value);
    }

    public static function from(
        DateTimeImmutable $value,
        ExpiresAt $expiresAt,
        ?DateTimeImmutable $now = null,
    ): self {
        $now ??= new DateTimeImmutable();

        if ($value <= $now) {
            throw InvalidResendAfterException::notInFuture();
        }

        if ($value >= $expiresAt->toDateTimeImmutable()) {
            throw InvalidResendAfterException::afterExpiration();
        }

        return new self($value);
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function isAllowed(DateTimeImmutable $now): bool
    {
        return $now >= $this->value;
    }
}
