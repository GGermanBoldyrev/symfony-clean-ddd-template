<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Command/Login/LoginCommandHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Command\Login;

use App\Identity\Application\Command\Login\LoginCommand;
use App\Identity\Application\Command\Login\LoginCommandHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use App\Tests\Unit\Identity\Application\TestDouble\FakePasswordHasher;
use App\Tests\Unit\Identity\Application\TestDouble\FakeTokenManager;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class LoginCommandHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    private ?FakePasswordHasher $hasher = null;

    private ?FakeTokenManager $tokens = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->hasher = new FakePasswordHasher();
        $this->tokens = new FakeTokenManager();
    }

    #[Test]
    public function itIssuesTokenPairForVerifiedUser(): void
    {
        $user = $this->user(verified: true);
        $this->users()->save($user);

        $pair = $this->handler()(new LoginCommand('user@example.com', 'secret123'));

        self::assertSame('access:' . $user->id->toString(), $pair->accessToken->toString());
        self::assertSame('refresh:' . $user->id->toString(), $pair->refreshToken->toString());
        self::assertSame(1, $this->hasher()->verifyCount());
        self::assertSame(1, $this->tokens()->accessIssueCount());
        self::assertSame(1, $this->tokens()->refreshIssueCount());
    }

    #[Test]
    public function itHidesInvalidEmailAsInvalidCredentials(): void
    {
        $this->expectAuthenticationException('Invalid credentials.');

        $this->handler()(new LoginCommand('not-an-email', 'secret123'));
    }

    #[Test]
    public function itRejectsMissingUserAsInvalidCredentials(): void
    {
        $this->expectAuthenticationException('Invalid credentials.');

        $this->handler()(new LoginCommand('missing@example.com', 'secret123'));
    }

    #[Test]
    public function itRejectsWrongPasswordAsInvalidCredentials(): void
    {
        $this->users()->save($this->user(verified: true));

        $this->expectAuthenticationException('Invalid credentials.');

        $this->handler()(new LoginCommand('user@example.com', 'wrong-password'));
    }

    #[Test]
    public function itRejectsUnverifiedUser(): void
    {
        $this->users()->save($this->user(verified: false));

        $this->expectAuthenticationException('Account is not verified.');

        $this->handler()(new LoginCommand('user@example.com', 'secret123'));
    }

    private function handler(): LoginCommandHandler
    {
        return new LoginCommandHandler($this->users(), $this->hasher(), $this->tokens());
    }

    private function users(): InMemoryUserRepository
    {
        return $this->users ?? throw new LogicException('User repository is not initialized.');
    }

    private function hasher(): FakePasswordHasher
    {
        return $this->hasher ?? throw new LogicException('Password hasher is not initialized.');
    }

    private function tokens(): FakeTokenManager
    {
        return $this->tokens ?? throw new LogicException('Token manager is not initialized.');
    }

    private function expectAuthenticationException(string $message): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage($message);
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
