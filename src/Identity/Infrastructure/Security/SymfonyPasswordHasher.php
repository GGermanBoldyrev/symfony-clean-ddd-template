<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\Port\PasswordHasherPort;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasherPort
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function hash(PlainPassword $password): HashedPassword
    {
        $principal = new SecurityUser('', '');

        $hash = $this->hasher->hashPassword($principal, $password->toString());

        return HashedPassword::fromRawHash($hash);
    }

    public function verify(PlainPassword $password, HashedPassword $hash): bool
    {
        $principal = new SecurityUser('', $hash->toString());

        return $this->hasher->isPasswordValid($principal, $password->toString());
    }
}
