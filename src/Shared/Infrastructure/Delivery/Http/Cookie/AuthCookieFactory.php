<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

final class AuthCookieFactory
{
    public const string REFRESH_TOKEN_COOKIE = 'refresh_token';

    public static function refreshToken(
        string $value,
        int $ttlSeconds,
        bool $secure,
    ): Cookie {
        return Cookie::create(
            name: self::REFRESH_TOKEN_COOKIE,
            value: $value,
            expire: time() + $ttlSeconds,
            path: '/',
            domain: null,
            secure: $secure,
            httpOnly: true,
            raw: false,
            sameSite: Cookie::SAMESITE_STRICT,
        );
    }

    public static function clearRefreshToken(): Cookie
    {
        return Cookie::create(
            name: self::REFRESH_TOKEN_COOKIE,
            value: '',
            expire: 1,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: Cookie::SAMESITE_STRICT,
        );
    }
}
