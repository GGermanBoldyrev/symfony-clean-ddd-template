<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Infrastructure\Delivery\Http\Controller;

use App\Identity\Application\Command\Login\LoginCommand;
use App\Identity\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Identity\Application\Command\Register\RegisterCommand;
use App\Identity\Application\Command\ResendVerificationCode\ResendVerificationCodeCommand;
use App\Identity\Application\Command\VerifyCode\VerifyCodeCommand;
use App\Identity\Application\Dto\CurrentUserDto;
use App\Identity\Application\Dto\TokenPairDto;
use App\Identity\Application\Query\GetCurrentUser\GetCurrentUserQuery;
use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Infrastructure\Delivery\Http\Controller\Authentication\LoginController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Authentication\LogoutController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Authentication\MeController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Authentication\RefreshController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Registration\RegisterController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Registration\ResendVerificationCodeController;
use App\Identity\Infrastructure\Delivery\Http\Controller\Registration\VerifyCodeController;
use App\Identity\Infrastructure\Delivery\Http\Cookie\AuthCookieFactory;
use App\Identity\Infrastructure\Security\SecurityUser;
use App\Tests\Unit\Identity\Application\TestDouble\FakeTokenManager;
use App\Tests\Unit\Shared\Application\TestDouble\RecordingMessageBus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends TestCase
{
    #[Test]
    public function registerDispatchesCommandAndReturnsCreatedResponse(): void
    {
        $bus = new RecordingMessageBus();
        $response = new RegisterController($bus)(
            $this->jsonRequest('{"email":"user@example.com","password":"secret123","data_policy":true}'),
        );

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(RegisterCommand::class, $message);
        self::assertSame('user@example.com', $message->email);
        self::assertSame('secret123', $message->password);
        self::assertTrue($message->dataPolicy);
        self::assertSame(201, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"message":"Verification code sent to your email."}}',
            $this->responseContent($response),
        );
    }

    #[Test]
    public function loginDispatchesCommandAndSetsRefreshCookie(): void
    {
        $tokens = new FakeTokenManager();
        $bus = new RecordingMessageBus(new TokenPairDto(
            AccessToken::fromString('access-token'),
            RefreshToken::fromString('refresh-token'),
        ));

        $response = new LoginController($bus, $tokens, secureCookies: true)(
            $this->jsonRequest('{"email":"user@example.com","password":"secret123"}'),
        );

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(LoginCommand::class, $message);
        self::assertSame('user@example.com', $message->email);
        self::assertSame('secret123', $message->password);
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"access_token":"access-token"}}',
            $this->responseContent($response),
        );

        $cookie = $this->firstCookie($response);
        self::assertSame(AuthCookieFactory::REFRESH_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('refresh-token', $cookie->getValue());
        self::assertTrue($cookie->isSecure());
    }

    #[Test]
    public function verifyCodeDispatchesCommandAndReturnsSuccessResponse(): void
    {
        $bus = new RecordingMessageBus();
        $response = new VerifyCodeController($bus)(
            $this->jsonRequest('{"email":"user@example.com","code":"123456"}'),
        );

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(VerifyCodeCommand::class, $message);
        self::assertSame('user@example.com', $message->email);
        self::assertSame('123456', $message->code);
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"message":"Account verified successfully."}}',
            $this->responseContent($response),
        );
    }

    #[Test]
    public function resendVerificationCodeDispatchesCommandAndReturnsPrivacyPreservingMessage(): void
    {
        $bus = new RecordingMessageBus();
        $response = new ResendVerificationCodeController($bus)(
            $this->jsonRequest('{"email":"user@example.com"}'),
        );

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(ResendVerificationCodeCommand::class, $message);
        self::assertSame('user@example.com', $message->email);
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"message":"If the account exists and is unverified, a new code has been sent."}}',
            $this->responseContent($response),
        );
    }

    #[Test]
    public function refreshReturnsUnauthorizedWhenRefreshCookieIsMissing(): void
    {
        $bus = new RecordingMessageBus(new TokenPairDto(
            AccessToken::fromString('access-token'),
            RefreshToken::fromString('refresh-token'),
        ));

        $response = (new RefreshController($bus, new FakeTokenManager(), secureCookies: false))(Request::create('/'));

        self::assertSame([], $bus->dispatched());
        self::assertSame(401, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":{"code":"auth.refresh_token_missing","message":"Refresh token cookie is missing."}}',
            $this->responseContent($response),
        );
    }

    #[Test]
    public function refreshDispatchesCommandAndRotatesRefreshCookie(): void
    {
        $tokens = new FakeTokenManager();
        $bus = new RecordingMessageBus(new TokenPairDto(
            AccessToken::fromString('new-access-token'),
            RefreshToken::fromString('new-refresh-token'),
        ));
        $request = Request::create('/');
        $request->cookies->set(AuthCookieFactory::REFRESH_TOKEN_COOKIE, 'old-refresh-token');

        $response = new RefreshController($bus, $tokens, secureCookies: false)($request);

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(RefreshTokenCommand::class, $message);
        self::assertSame('old-refresh-token', $message->refreshToken);
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"access_token":"new-access-token"}}',
            $this->responseContent($response),
        );

        $cookie = $this->firstCookie($response);
        self::assertSame('new-refresh-token', $cookie->getValue());
        self::assertFalse($cookie->isSecure());
    }

    #[Test]
    public function logoutClearsRefreshCookie(): void
    {
        $response = new LogoutController()();

        self::assertSame(204, $response->getStatusCode());
        $cookie = $this->firstCookie($response);
        self::assertSame(AuthCookieFactory::REFRESH_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('', $cookie->getValue());
        self::assertSame(1, $cookie->getExpiresTime());
    }

    #[Test]
    public function meDispatchesQueryAndReturnsCurrentUser(): void
    {
        $userId = UserId::generate();
        $bus = new RecordingMessageBus(new CurrentUserDto(
            id: $userId->toString(),
            email: 'user@example.com',
            isVerified: true,
            verifiedAt: '2024-01-01T12:05:00+00:00',
            dataPolicyAcceptedAt: '2024-01-01T12:00:00+00:00',
        ));
        $securityUser = new SecurityUser($userId, HashedPassword::fromRawHash('hash'));

        $response = new MeController($bus)($securityUser);

        $message = $bus->dispatched()[0];
        self::assertInstanceOf(GetCurrentUserQuery::class, $message);
        self::assertSame($userId->toString(), $message->userId);
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"id":"' . $userId->toString() . '","email":"user@example.com","is_verified":true,"verified_at":"2024-01-01T12:05:00+00:00","data_policy_accepted_at":"2024-01-01T12:00:00+00:00"}}',
            $this->responseContent($response),
        );
    }

    private function jsonRequest(string $content): Request
    {
        return Request::create('/', 'POST', [], [], [], [], $content);
    }

    private function responseContent(Response $response): string
    {
        $content = $response->getContent();
        self::assertIsString($content);

        return $content;
    }

    private function firstCookie(Response $response): Cookie
    {
        $cookies = $response->headers->getCookies();
        self::assertArrayHasKey(0, $cookies);

        return $cookies[0];
    }
}
