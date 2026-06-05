<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/VerificationCode/VerificationCodeIdTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\VerificationCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidVerificationCodeIdException;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationCodeIdTest extends TestCase
{
    #[Test]
    public function itGeneratesValidUuid(): void
    {
        $id = VerificationCodeId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id->toString(),
        );
    }

    #[Test]
    public function itAcceptsValidUuidString(): void
    {
        $uuid = '018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b';

        self::assertSame($uuid, VerificationCodeId::fromString($uuid)->toString());
    }

    #[Test]
    public function itRejectsInvalidUuidString(): void
    {
        $this->expectException(InvalidVerificationCodeIdException::class);

        VerificationCodeId::fromString('not-a-uuid');
    }

    #[Test]
    public function itComparesEqualIds(): void
    {
        $uuid = '018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b';

        self::assertTrue(VerificationCodeId::fromString($uuid)->equals(VerificationCodeId::fromString($uuid)));
    }
}
