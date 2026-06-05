<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\Port\PasswordHasherPort;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasherPort
{
    private const string MOCK_USER_ID = '00000000-0000-0000-0000-000000000000';

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function hash(PlainPassword $password): HashedPassword
    {
        $userId = UserId::fromString(self::MOCK_USER_ID);
        $passwordHash = HashedPassword::fromRawHash('');

        $principal = new SecurityUser($userId, $passwordHash);

        $hash = $this->hasher->hashPassword($principal, $password->toString());

        return HashedPassword::fromRawHash($hash);
    }

    public function verify(PlainPassword $password, HashedPassword $hash): bool
    {
        $userId = UserId::fromString(self::MOCK_USER_ID);

        $principal = new SecurityUser($userId, $hash);

        return $this->hasher->isPasswordValid($principal, $password->toString());
    }
}
