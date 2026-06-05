<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/VerificationCode/ResendAfterTest.php

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
        $expiresAt = ExpiresAt::from(new DateTimeImmutable('+1 hour'));
        $time = new DateTimeImmutable('+1 minute');
        $resendAfter = ResendAfter::from($time, $expiresAt);

        self::assertSame($time, $resendAfter->toDateTimeImmutable());
        self::assertFalse($resendAfter->isAllowed());
    }

    #[Test]
    public function itRejectsPastTimestamp(): void
    {
        $this->expectException(InvalidResendAfterException::class);

        ResendAfter::from(new DateTimeImmutable('-1 second'), ExpiresAt::from(new DateTimeImmutable('+1 hour')));
    }

    #[Test]
    public function itRejectsTimestampAfterExpiration(): void
    {
        $expiresAt = ExpiresAt::from(new DateTimeImmutable('+1 minute'));

        $this->expectException(InvalidResendAfterException::class);

        ResendAfter::from(new DateTimeImmutable('+2 minutes'), $expiresAt);
    }

    #[Test]
    public function itCanReconstructPastTimestampForPersistence(): void
    {
        $resendAfter = ResendAfter::fromDateTimeImmutable(new DateTimeImmutable('-1 second'));

        self::assertTrue($resendAfter->isAllowed());
    }
}
