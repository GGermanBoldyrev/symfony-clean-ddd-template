<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/User/VerifiedAtTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\ValueObject\User\VerifiedAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerifiedAtTest extends TestCase
{
    #[Test]
    public function itCreatesCurrentTimestamp(): void
    {
        $before = new DateTimeImmutable();
        $verifiedAt = VerifiedAt::now();

        self::assertGreaterThanOrEqual($before, $verifiedAt->toDateTimeImmutable());
    }

    #[Test]
    public function itCanBeReconstructedFromDateTimeImmutable(): void
    {
        $time = new DateTimeImmutable('2024-01-01 12:00:00');
        $verifiedAt = VerifiedAt::fromDateTimeImmutable($time);

        self::assertSame($time, $verifiedAt->toDateTimeImmutable());
    }

    #[Test]
    public function itComparesEqualTimestamps(): void
    {
        $time = new DateTimeImmutable('2024-01-01 12:00:00');

        self::assertTrue(
            VerifiedAt::fromDateTimeImmutable($time)->equals(VerifiedAt::fromDateTimeImmutable($time)),
        );
    }
}
