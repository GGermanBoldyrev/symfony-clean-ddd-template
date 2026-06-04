<?php

declare(strict_types=1);

namespace App\Identity\Domain\Service;

use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use DateTimeImmutable;

final class VerificationCodePolicy
{
    private const int TTL_SECONDS = 900; // 15 min

    private const int RESEND_COOLDOWN_SECONDS = 60; // 1 min

    private const int MAX_ATTEMPTS = 5;

    public static function expiresAt(DateTimeImmutable $now): ExpiresAt
    {
        return ExpiresAt::from(
            $now->modify(sprintf('+%d seconds', self::TTL_SECONDS)),
        );
    }

    public static function resendAfter(DateTimeImmutable $now, ExpiresAt $expiresAt): ResendAfter
    {
        return ResendAfter::from(
            $now->modify(sprintf('+%d seconds', self::RESEND_COOLDOWN_SECONDS)),
            $expiresAt,
        );
    }

    public static function maxAttempts(): MaxAttempts
    {
        return MaxAttempts::fromInt(self::MAX_ATTEMPTS);
    }
}
