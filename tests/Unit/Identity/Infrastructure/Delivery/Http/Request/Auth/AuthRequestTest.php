<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Delivery/Http/Request/Auth/AuthRequestTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Delivery\Http\Request\Auth;

use App\Identity\Infrastructure\Delivery\Http\Request\Authentication\LoginRequest;
use App\Identity\Infrastructure\Delivery\Http\Request\Registration\RegisterRequest;
use App\Identity\Infrastructure\Delivery\Http\Request\Registration\ResendVerificationCodeRequest;
use App\Identity\Infrastructure\Delivery\Http\Request\Registration\VerifyCodeRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class AuthRequestTest extends TestCase
{
    #[Test]
    public function itBuildsRegisterRequestFromJson(): void
    {
        $request = RegisterRequest::fromRequest($this->request('{"email":"user@example.com","password":"secret123","data_policy":true}'));

        self::assertSame('user@example.com', $request->email);
        self::assertSame('secret123', $request->password);
        self::assertTrue($request->dataPolicy);
    }

    #[Test]
    public function itBuildsLoginRequestFromJson(): void
    {
        $request = LoginRequest::fromRequest($this->request('{"email":"user@example.com","password":"secret123"}'));

        self::assertSame('user@example.com', $request->email);
        self::assertSame('secret123', $request->password);
    }

    #[Test]
    public function itBuildsVerifyCodeRequestFromJson(): void
    {
        $request = VerifyCodeRequest::fromRequest($this->request('{"email":"user@example.com","code":"123456"}'));

        self::assertSame('user@example.com', $request->email);
        self::assertSame('123456', $request->code);
    }

    #[Test]
    public function itBuildsResendVerificationCodeRequestFromJson(): void
    {
        $request = ResendVerificationCodeRequest::fromRequest($this->request('{"email":"user@example.com"}'));

        self::assertSame('user@example.com', $request->email);
    }

    private function request(string $content): Request
    {
        return Request::create('/', 'POST', [], [], [], [], $content);
    }
}
