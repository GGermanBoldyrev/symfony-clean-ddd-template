<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/TokenValueObjectTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject;

use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TokenValueObjectTest extends TestCase
{
    #[Test]
    public function itCreatesAccessTokenFromString(): void
    {
        $token = AccessToken::fromString('access-token');

        self::assertSame('access-token', $token->toString());
        self::assertSame('access-token', (string) $token);
    }

    #[Test]
    public function itCreatesRefreshTokenFromString(): void
    {
        $token = RefreshToken::fromString('refresh-token');

        self::assertSame('refresh-token', $token->toString());
        self::assertSame('refresh-token', (string) $token);
    }

    #[Test]
    public function itComparesTokenValues(): void
    {
        self::assertTrue(AccessToken::fromString('same')->equals(AccessToken::fromString('same')));
        self::assertFalse(RefreshToken::fromString('one')->equals(RefreshToken::fromString('two')));
    }
}
