<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidResendAfterException;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResendAfterTest extends TestCase
{
    #[Test]
    public function itAcceptsFutureTimestampBeforeExpiration(): void
    {
        $now = new DateTimeImmutable();
        $futureMinute = $now->modify('+1 minute');
        $futureHour = $now->modify('+1 hour');

        $expiresAt = ExpiresAt::from($futureHour, $now);
        $resendAfter = ResendAfter::from($futureMinute, $expiresAt, $now);

        self::assertEquals($futureMinute, $resendAfter->toDateTimeImmutable());
        self::assertFalse($resendAfter->isAllowed($now));
    }

    #[Test]
    public function itRejectsPastTimestamp(): void
    {
        $now = new DateTimeImmutable();
        $past = $now->modify('-1 minute');
        $future = $now->modify('+1 hour');

        $this->expectException(InvalidResendAfterException::class);

        ResendAfter::from($past, ExpiresAt::from($future), $now);
    }

    #[Test]
    public function itRejectsTimestampAfterExpiration(): void
    {
        $now = new DateTimeImmutable();
        $expiresTime = $now->modify('+1 minute');
        $resendAfterTime = $now->modify('+2 minutes');

        $expiresAt = ExpiresAt::from($expiresTime, $now);

        $this->expectException(InvalidResendAfterException::class);

        ResendAfter::from($resendAfterTime, $expiresAt, $now);
    }

    #[Test]
    public function itCanReconstructPastTimestampForPersistence(): void
    {
        $now = new DateTimeImmutable();
        $past = $now->modify('-1 second');

        $resendAfter = ResendAfter::fromDateTimeImmutable($past);

        self::assertTrue($resendAfter->isAllowed($now));
    }
}
