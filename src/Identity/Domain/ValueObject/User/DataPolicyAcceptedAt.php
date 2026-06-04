<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;
use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class DataPolicyAcceptedAt extends DateTimeValueObject
{
    private function __construct(DateTimeImmutable $value)
    {
        parent::__construct($value);
    }

    /**
     * @throws DataPolicyNotAcceptedException
     */
    public static function accept(bool $accepted): self
    {
        if (!$accepted) {
            throw DataPolicyNotAcceptedException::notAccepted();
        }

        return new self(new DateTimeImmutable());
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }
}
