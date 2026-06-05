<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/VerificationCode/AttemptCountTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidAttemptCountException;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AttemptCountTest extends TestCase
{
    #[Test]
    public function itStartsAtZero(): void
    {
        self::assertSame(0, AttemptCount::zero()->toInt());
    }

    #[Test]
    public function itAcceptsPositiveValue(): void
    {
        self::assertSame(3, AttemptCount::fromInt(3)->toInt());
    }

    #[Test]
    public function itRejectsNegativeValue(): void
    {
        $this->expectException(InvalidAttemptCountException::class);

        AttemptCount::fromInt(-1);
    }

    #[Test]
    public function itIncrementsImmutably(): void
    {
        $attempts = AttemptCount::fromInt(2);
        $incremented = $attempts->increment();

        self::assertSame(2, $attempts->toInt());
        self::assertSame(3, $incremented->toInt());
    }

    #[Test]
    public function itComparesEqualValues(): void
    {
        self::assertTrue(AttemptCount::fromInt(2)->equals(AttemptCount::fromInt(2)));
    }
}
