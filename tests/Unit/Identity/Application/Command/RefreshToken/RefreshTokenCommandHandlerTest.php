<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Command/RefreshToken/RefreshTokenCommandHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Command\RefreshToken;

use App\Identity\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Identity\Application\Command\RefreshToken\RefreshTokenCommandHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use App\Tests\Unit\Identity\Application\TestDouble\FakeTokenManager;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class RefreshTokenCommandHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    private ?FakeTokenManager $tokens = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->tokens = new FakeTokenManager();
    }

    #[Test]
    public function itIssuesNewTokenPairForVerifiedUser(): void
    {
        $user = $this->user(verified: true);
        $this->users()->save($user);
        $this->tokens()->parseRefreshTokenAs($user->id);

        $pair = $this->handler()(new RefreshTokenCommand('refresh-token'));

        self::assertSame('access:' . $user->id->toString(), $pair->accessToken->toString());
        self::assertSame('refresh:' . $user->id->toString(), $pair->refreshToken->toString());
    }

    #[Test]
    public function itRejectsInvalidRefreshToken(): void
    {
        $this->expectAuthenticationException();

        $this->handler()(new RefreshTokenCommand('bad-token'));
    }

    #[Test]
    public function itRejectsRefreshTokenForMissingUser(): void
    {
        $this->tokens()->parseRefreshTokenAs(UserId::generate());

        $this->expectAuthenticationException();

        $this->handler()(new RefreshTokenCommand('refresh-token'));
    }

    #[Test]
    public function itRejectsRefreshTokenForUnverifiedUser(): void
    {
        $user = $this->user(verified: false);
        $this->users()->save($user);
        $this->tokens()->parseRefreshTokenAs($user->id);

        $this->expectAuthenticationException();

        $this->handler()(new RefreshTokenCommand('refresh-token'));
    }

    private function handler(): RefreshTokenCommandHandler
    {
        return new RefreshTokenCommandHandler($this->users(), $this->tokens());
    }

    private function users(): InMemoryUserRepository
    {
        return $this->users ?? throw new LogicException('User repository is not initialized.');
    }

    private function tokens(): FakeTokenManager
    {
        return $this->tokens ?? throw new LogicException('Token manager is not initialized.');
    }

    private function expectAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Refresh token is invalid or expired.');
    }

    private function user(bool $verified): User
    {
        return new User(
            id: UserId::generate(),
            email: Email::fromString('user@example.com'),
            passwordHash: HashedPassword::fromRawHash('hashed:secret123'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:00:00')),
            createdAt: new DateTimeImmutable('2024-01-01 12:00:00'),
            verifiedAt: $verified ? VerifiedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:05:00')) : null,
        );
    }
}
