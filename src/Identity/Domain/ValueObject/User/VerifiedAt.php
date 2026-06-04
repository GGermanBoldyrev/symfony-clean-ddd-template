<?php


declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class VerifiedAt extends DateTimeValueObject
{
    private function __construct(DateTimeImmutable $value)
    {
        parent::__construct($value);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }
}
