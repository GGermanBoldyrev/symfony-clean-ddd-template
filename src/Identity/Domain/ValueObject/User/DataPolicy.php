<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;

final readonly class DataPolicy
{
    private function __construct(
        private bool $accepted,
    ) {}

    public static function accepted(): self
    {
        return new self(true);
    }

    public static function fromBool(bool $accepted): self
    {
        if (!$accepted) {
            throw DataPolicyNotAcceptedException::notAccepted();
        }

        return new self($accepted);
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function equals(self $other): bool
    {
        return $this->accepted === $other->accepted;
    }
}
