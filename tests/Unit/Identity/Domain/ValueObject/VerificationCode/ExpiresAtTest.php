<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/VerificationCode/ExpiresAtTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidExpiresAtException;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExpiresAtTest extends TestCase
{
    #[Test]
    public function itAcceptsFutureTimestamp(): void
    {
        $time = new DateTimeImmutable('+1 hour');
        $expiresAt = ExpiresAt::from($time);

        self::assertSame($time, $expiresAt->toDateTimeImmutable());
        self::assertFalse($expiresAt->isExpired());
    }

    #[Test]
    public function itRejectsPastTimestamp(): void
    {
        $this->expectException(InvalidExpiresAtException::class);

        ExpiresAt::from(new DateTimeImmutable('-1 second'));
    }

    #[Test]
    public function itCanReconstructExpiredTimestamp(): void
    {
        $expiresAt = ExpiresAt::fromDateTimeImmutable(new DateTimeImmutable('-1 second'));

        self::assertTrue($expiresAt->isExpired());
    }
}
