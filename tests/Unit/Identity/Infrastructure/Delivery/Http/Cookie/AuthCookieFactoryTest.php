<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Delivery/Http/Cookie/AuthCookieFactoryTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Delivery\Http\Cookie;

use App\Identity\Infrastructure\Delivery\Http\Cookie\AuthCookieFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthCookieFactoryTest extends TestCase
{
    #[Test]
    public function itCreatesRefreshTokenCookie(): void
    {
        $before = time();
        $cookie = AuthCookieFactory::refreshToken('refresh-token', 3600, secure: false);

        self::assertSame(AuthCookieFactory::REFRESH_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('refresh-token', $cookie->getValue());
        self::assertSame('/', $cookie->getPath());
        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertSame(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
        self::assertGreaterThanOrEqual($before + 3600, $cookie->getExpiresTime());
    }

    #[Test]
    public function itClearsRefreshTokenCookie(): void
    {
        $cookie = AuthCookieFactory::clearRefreshToken();

        self::assertSame(AuthCookieFactory::REFRESH_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('', $cookie->getValue());
        self::assertSame(1, $cookie->getExpiresTime());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
    }
}
