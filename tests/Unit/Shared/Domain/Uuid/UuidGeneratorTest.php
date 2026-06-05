<?php

declare(strict_types=1);

// tests/Unit/Shared/Domain/Uuid/UuidGeneratorTest.php

namespace App\Tests\Unit\Shared\Domain\Uuid;

use App\Shared\Domain\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function itGeneratesValidUuidV7String(): void
    {
        $uuid = UuidGenerator::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid,
        );
        self::assertTrue(UuidGenerator::isValid($uuid));
    }

    #[Test]
    public function itRejectsInvalidUuidString(): void
    {
        self::assertFalse(UuidGenerator::isValid('not-a-uuid'));
    }
}
