<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/InMemory/InMemoryUserRepository.php

namespace App\Tests\Unit\Identity\Infrastructure\InMemory;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\UserId;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $storage = [];

    private int $saveCount = 0;

    public function findById(UserId $id): ?User
    {
        return $this->storage[$id->toString()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->storage as $user) {
            if ($user->email->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function save(User $user): void
    {
        $this->storage[$user->id->toString()] = $user;
        ++$this->saveCount;
    }

    /**
     * @return array<string, User>
     */
    public function all(): array
    {
        return $this->storage;
    }

    public function count(): int
    {
        return \count($this->storage);
    }

    public function saveCount(): int
    {
        return $this->saveCount;
    }

    public function clear(): void
    {
        $this->storage = [];
        $this->saveCount = 0;
    }
}
