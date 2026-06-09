<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly UserId $userId,
        private readonly HashedPassword $passwordHash,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->userId->toString();
    }

    public function getPassword(): string
    {
        return $this->passwordHash->toString();
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase — we never hold plain passwords here.
    }
}
