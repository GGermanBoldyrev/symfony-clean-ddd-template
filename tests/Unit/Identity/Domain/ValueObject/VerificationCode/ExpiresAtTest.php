<?php

declare(strict_types=1);

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
        $now = new DateTimeImmutable();
        $futureTime = $now->modify('+1 hour');

        $expiresAt = ExpiresAt::from($futureTime, $now);

        self::assertEquals($futureTime, $expiresAt->toDateTimeImmutable());
        self::assertFalse($expiresAt->isExpired($now));
    }

    #[Test]
    public function itRejectsPastTimestamp(): void
    {
        $this->expectException(InvalidExpiresAtException::class);

        $now = new DateTimeImmutable();
        $pastTime = $now->modify('-1 second');

        ExpiresAt::from($pastTime, $now);
    }

    #[Test]
    public function itCanReconstructExpiredTimestamp(): void
    {
        $now = new DateTimeImmutable();
        $pastTime = $now->modify('-1 second');

        $expiresAt = ExpiresAt::fromDateTimeImmutable($pastTime);

        self::assertTrue($expiresAt->isExpired($now));
    }
}
