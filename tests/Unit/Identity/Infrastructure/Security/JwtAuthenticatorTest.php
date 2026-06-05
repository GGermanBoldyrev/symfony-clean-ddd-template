<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Security/JwtAuthenticatorTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Security;

use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Infrastructure\Security\JwtAuthenticator;
use App\Tests\Unit\Identity\Application\TestDouble\FakeTokenManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final class JwtAuthenticatorTest extends TestCase
{
    #[Test]
    public function itSupportsRequestsWithBearerAuthorizationHeader(): void
    {
        $authenticator = new JwtAuthenticator(new FakeTokenManager());

        self::assertTrue($authenticator->supports($this->request('Bearer token')));
        self::assertFalse($authenticator->supports($this->request('Basic token')));
    }

    #[Test]
    public function itAuthenticatesValidBearerToken(): void
    {
        $userId = UserId::generate();
        $tokens = new FakeTokenManager();
        $tokens->parseAccessTokenAs($userId);
        $authenticator = new JwtAuthenticator($tokens);

        $passport = $authenticator->authenticate($this->request('Bearer access-token'));
        $badge = $passport->getBadge(UserBadge::class);

        self::assertInstanceOf(UserBadge::class, $badge);
        self::assertSame($userId->toString(), $badge->getUserIdentifier());
    }

    #[Test]
    public function itRejectsEmptyBearerToken(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('JWT token is missing.');

        (new JwtAuthenticator(new FakeTokenManager()))->authenticate($this->request('Bearer '));
    }

    #[Test]
    public function itRejectsInvalidBearerToken(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('JWT token is invalid or expired.');

        (new JwtAuthenticator(new FakeTokenManager()))->authenticate($this->request('Bearer invalid-token'));
    }

    #[Test]
    public function itReturnsApiResponseOnAuthenticationFailure(): void
    {
        $response = (new JwtAuthenticator(new FakeTokenManager()))->onAuthenticationFailure(
            Request::create('/api/v1/me'),
            new CustomUserMessageAuthenticationException('JWT token is invalid or expired.'),
        );

        self::assertSame(401, $response->getStatusCode());
        $content = $response->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":{"code":"auth.unauthorized","message":"JWT token is invalid or expired."}}',
            $content,
        );
    }

    private function request(string $authorization): Request
    {
        return Request::create('/', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => $authorization]);
    }
}
