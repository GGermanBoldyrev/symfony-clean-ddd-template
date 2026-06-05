<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Security/UserProviderTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Security;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Infrastructure\Security\SecurityUser;
use App\Identity\Infrastructure\Security\UserProvider;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException as SymfonyUserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserProviderTest extends TestCase
{
    #[Test]
    public function itLoadsSecurityUserByIdentifier(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->user();
        $repository->save($user);

        $securityUser = (new UserProvider($repository))->loadUserByIdentifier($user->id->toString());

        self::assertSame($user->id->toString(), $securityUser->getUserIdentifier());
        self::assertSame('hashed-password', $securityUser->getPassword());
    }

    #[Test]
    public function itThrowsSymfonyUserNotFoundExceptionForMissingUser(): void
    {
        $userId = UserId::generate();

        try {
            (new UserProvider(new InMemoryUserRepository()))->loadUserByIdentifier($userId->toString());
            self::fail('Expected user not found exception.');
        } catch (SymfonyUserNotFoundException $exception) {
            self::assertSame($userId->toString(), $exception->getUserIdentifier());
        }
    }

    #[Test]
    public function itRefreshesSupportedSecurityUserFromRepository(): void
    {
        $repository = new InMemoryUserRepository();
        $user = $this->user();
        $repository->save($user);
        $provider = new UserProvider($repository);

        $refreshed = $provider->refreshUser(new SecurityUser($user->id, HashedPassword::fromRawHash('old-hash')));

        self::assertSame('hashed-password', $refreshed->getPassword());
    }

    #[Test]
    public function itRejectsUnsupportedSecurityUserOnRefresh(): void
    {
        $this->expectException(UnsupportedUserException::class);

        (new UserProvider(new InMemoryUserRepository()))->refreshUser(new UnsupportedSymfonyUser());
    }

    #[Test]
    public function itSupportsOnlySecurityUserClass(): void
    {
        $provider = new UserProvider(new InMemoryUserRepository());

        self::assertTrue($provider->supportsClass(SecurityUser::class));
        self::assertFalse($provider->supportsClass(UnsupportedSymfonyUser::class));
    }

    private function user(): User
    {
        return new User(
            id: UserId::generate(),
            email: Email::fromString('user@example.com'),
            passwordHash: HashedPassword::fromRawHash('hashed-password'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:00:00')),
            createdAt: new DateTimeImmutable('2024-01-01 12:00:00'),
        );
    }
}

final class UnsupportedSymfonyUser implements UserInterface
{
    public function getUserIdentifier(): string
    {
        return 'unsupported';
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }
}
