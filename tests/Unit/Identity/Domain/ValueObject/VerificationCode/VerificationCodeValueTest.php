<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidVerificationCodeValueException;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationCodeValueTest extends TestCase
{
    #[Test]
    public function itAcceptsValidSixDigitCode(): void
    {
        $code = VerificationCodeValue::fromString('123456');

        self::assertSame('123456', $code->toString());
    }

    #[Test]
    public function itAcceptsLeadingZeroCode(): void
    {
        $code = VerificationCodeValue::fromString('000001');

        self::assertSame('000001', $code->toString());
    }

    #[Test]
    public function itAcceptsAllZerosCode(): void
    {
        $code = VerificationCodeValue::fromString('000000');

        self::assertSame('000000', $code->toString());
    }

    #[Test]
    #[DataProvider('invalidCodeProvider')]
    public function itRejectsInvalidCode(string $value): void
    {
        $this->expectException(InvalidVerificationCodeValueException::class);

        VerificationCodeValue::fromString($value);
    }

    #[Test]
    public function itMatchesEqualCodes(): void
    {
        $a = VerificationCodeValue::fromString('123456');
        $b = VerificationCodeValue::fromString('123456');

        self::assertTrue($a->matches($b));
    }

    #[Test]
    public function itDoesNotMatchDifferentCodes(): void
    {
        $a = VerificationCodeValue::fromString('123456');
        $b = VerificationCodeValue::fromString('654321');

        self::assertFalse($a->matches($b));
    }

    /** @return array<string, array{string}> */
    public static function invalidCodeProvider(): array
    {
        return [
            'too-short' => ['12345'],
            'too-long' => ['1234567'],
            'with-letters' => ['12345a'],
            'empty' => [''],
            'with-spaces' => ['12 456'],
        ];
    }
}
