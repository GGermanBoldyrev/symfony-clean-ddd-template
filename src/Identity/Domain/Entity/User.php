<?php

declare(strict_types=1);

namespace App\Identity\Domain\Entity;

use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use DateTimeImmutable;

final class User
{
    public private(set) ?VerifiedAt $verifiedAt;
    public private(set) DateTimeImmutable $updatedAt;

    public function __construct(
        public readonly UserId $id,
        public readonly Email $email,
        public readonly HashedPassword $passwordHash,
        public readonly DataPolicyAcceptedAt $dataPolicyAcceptedAt,
        public readonly DateTimeImmutable $createdAt,
        ?VerifiedAt $verifiedAt = null,
    ) {
        $this->verifiedAt = $verifiedAt;
        $this->updatedAt = $createdAt;
    }

    public static function register(
        UserId $id,
        Email $email,
        HashedPassword $passwordHash,
        DataPolicyAcceptedAt $dataPolicyAcceptedAt,
    ): self {
        return new self(
            id: $id,
            email: $email,
            passwordHash: $passwordHash,
            dataPolicyAcceptedAt: $dataPolicyAcceptedAt,
            createdAt: new DateTimeImmutable(),
        );
    }

    public function verify(): void
    {
        if ($this->verifiedAt !== null) {
            throw new UserAlreadyVerifiedException($this->email);
        }

        $this->verifiedAt = VerifiedAt::now();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isVerified(): bool
    {
        return $this->verifiedAt !== null;
    }
}
