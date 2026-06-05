<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Security/SymfonyPasswordHasherTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Security;

use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use App\Identity\Infrastructure\Security\SymfonyPasswordHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class SymfonyPasswordHasherTest extends TestCase
{
    #[Test]
    public function itHashesPlainPasswordThroughSymfonyHasher(): void
    {
        $adapter = new SymfonyPasswordHasher(new DeterministicUserPasswordHasher());

        $hash = $adapter->hash(PlainPassword::fromString('secret123'));

        self::assertSame('hashed:secret123', $hash->toString());
    }

    #[Test]
    public function itVerifiesPlainPasswordThroughSymfonyHasher(): void
    {
        $adapter = new SymfonyPasswordHasher(new DeterministicUserPasswordHasher());

        self::assertTrue($adapter->verify(
            PlainPassword::fromString('secret123'),
            HashedPassword::fromRawHash('hashed:secret123'),
        ));
        self::assertFalse($adapter->verify(
            PlainPassword::fromString('secret123'),
            HashedPassword::fromRawHash('hashed:other'),
        ));
    }
}

final class DeterministicUserPasswordHasher implements UserPasswordHasherInterface
{
    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        return 'hashed:' . $plainPassword;
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        return $user->getPassword() === 'hashed:' . $plainPassword;
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return false;
    }
}
