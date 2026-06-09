<?php

declare(strict_types=1);

namespace App\Identity\Domain\Entity;

use App\Identity\Domain\ValueObject\PasswordResetCode\AttemptCount;
use App\Identity\Domain\ValueObject\PasswordResetCode\ExpiresAt;
use App\Identity\Domain\ValueObject\PasswordResetCode\MaxAttempts;
use App\Identity\Domain\ValueObject\PasswordResetCode\PasswordResetCodeId;
use App\Identity\Domain\ValueObject\PasswordResetCode\PasswordResetCodeValue;
use App\Identity\Domain\ValueObject\PasswordResetCode\ResendAfter;
use App\Identity\Domain\ValueObject\User\Email;
use DateTimeImmutable;

final class PasswordResetCode
{
    public private(set) AttemptCount $attempts;
    public private(set) DateTimeImmutable $updatedAt;

    public function __construct(
        public readonly PasswordResetCodeId $id,
        public readonly Email $email,
        public readonly PasswordResetCodeValue $code,
        AttemptCount $attempts,
        public readonly MaxAttempts $maxAttempts,
        public readonly ExpiresAt $expiresAt,
        public readonly ResendAfter $resendAfter,
        public readonly DateTimeImmutable $createdAt,
    ) {
        $this->attempts = $attempts;
        $this->updatedAt = $this->createdAt;
    }

    public static function issue(
        PasswordResetCodeId $id,
        Email $email,
        PasswordResetCodeValue $code,
        MaxAttempts $maxAttempts,
        ExpiresAt $expiresAt,
        ResendAfter $resendAfter,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            email: $email,
            code: $code,
            attempts: AttemptCount::zero(),
            maxAttempts: $maxAttempts,
            expiresAt: $expiresAt,
            resendAfter: $resendAfter,
            createdAt: $now,
        );
    }
}
