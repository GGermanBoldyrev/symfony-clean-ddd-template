<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/Service/VerificationCodePolicyTest.php

namespace App\Tests\Unit\Identity\Domain\Service;

use App\Identity\Domain\Service\VerificationCodePolicy;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationCodePolicyTest extends TestCase
{
    #[Test]
    public function itCreatesExpirationFifteenMinutesAfterNow(): void
    {
        $now = new DateTimeImmutable('+1 second');
        $expiresAt = VerificationCodePolicy::expiresAt($now);

        self::assertSame(900, $expiresAt->toDateTimeImmutable()->getTimestamp() - $now->getTimestamp());
    }

    #[Test]
    public function itCreatesResendCooldownOneMinuteAfterNowAndBeforeExpiration(): void
    {
        $now = new DateTimeImmutable('+1 second');
        $expiresAt = VerificationCodePolicy::expiresAt($now);
        $resendAfter = VerificationCodePolicy::resendAfter($now, $expiresAt);

        self::assertSame(60, $resendAfter->toDateTimeImmutable()->getTimestamp() - $now->getTimestamp());
        self::assertLessThan($expiresAt->toDateTimeImmutable(), $resendAfter->toDateTimeImmutable());
    }

    #[Test]
    public function itUsesFiveMaxAttempts(): void
    {
        self::assertSame(5, VerificationCodePolicy::maxAttempts()->toInt());
    }
}
