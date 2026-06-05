<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/VerificationCode/MaxAttemptsTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidMaxAttemptsException;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MaxAttemptsTest extends TestCase
{
    #[Test]
    public function itAcceptsPositiveValue(): void
    {
        self::assertSame(5, MaxAttempts::fromInt(5)->toInt());
    }

    #[Test]
    #[DataProvider('invalidMaxAttemptsProvider')]
    public function itRejectsNonPositiveValue(int $value): void
    {
        $this->expectException(InvalidMaxAttemptsException::class);

        MaxAttempts::fromInt($value);
    }

    #[Test]
    public function itReportsExceededWhenAttemptsReachLimit(): void
    {
        $maxAttempts = MaxAttempts::fromInt(3);

        self::assertFalse($maxAttempts->isExceeded(AttemptCount::fromInt(2)));
        self::assertTrue($maxAttempts->isExceeded(AttemptCount::fromInt(3)));
        self::assertTrue($maxAttempts->isExceeded(AttemptCount::fromInt(4)));
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidMaxAttemptsProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
        ];
    }
}
