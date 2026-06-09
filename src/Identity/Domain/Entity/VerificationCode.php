<?php

declare(strict_types=1);

namespace App\Identity\Domain\Entity;

use App\Identity\Domain\Exception\VerificationCode\CodeExpiredException;
use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use DateTimeImmutable;

final class VerificationCode
{
    public private(set) AttemptCount $attempts;
    public private(set) DateTimeImmutable $updatedAt;

    public function __construct(
        public readonly VerificationCodeId $id,
        public readonly Email $email,
        public readonly VerificationCodeValue $code,
        AttemptCount $attempts,
        public readonly MaxAttempts $maxAttempts,
        public readonly ExpiresAt $expiresAt,
        public readonly ResendAfter $resendAfter,
        public readonly DateTimeImmutable $createdAt,
    ) {
        $this->attempts = $attempts;
        $this->updatedAt = $createdAt;
    }

    public static function issue(
        VerificationCodeId $id,
        Email $email,
        VerificationCodeValue $code,
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

    /**
     * @throws CodeExpiredException when the code TTL has passed
     * @throws MaxAttemptsExceededException when the attempt ceiling is hit
     * @throws InvalidCodeException when the submitted code is wrong
     */
    public function verify(VerificationCodeValue $submitted): void
    {
        $now = new DateTimeImmutable();

        if ($this->expiresAt->isExpired($now)) {
            throw CodeExpiredException::expired();
        }

        if ($this->maxAttempts->isExceeded($this->attempts)) {
            throw MaxAttemptsExceededException::exceeded();
        }

        if (!$this->code->matches($submitted)) {
            $this->attempts = $this->attempts->increment();
            $this->updatedAt = new DateTimeImmutable();

            if ($this->maxAttempts->isExceeded($this->attempts)) {
                throw MaxAttemptsExceededException::exceeded();
            }

            throw InvalidCodeException::mismatch();
        }

        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws ResendCooldownException when the resend window is not open
     */
    public function assertCanResend(): void
    {
        $now = new DateTimeImmutable();

        if (!$this->resendAfter->isAllowed($now)) {
            throw ResendCooldownException::before($this->resendAfter->toDateTimeImmutable());
        }
    }

    public function isExpired(): bool
    {
        $now = new DateTimeImmutable();

        return $this->expiresAt->isExpired($now);
    }

    public function isExhausted(): bool
    {
        return $this->maxAttempts->isExceeded($this->attempts);
    }
}
