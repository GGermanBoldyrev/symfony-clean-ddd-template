<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/TestDouble/FakePasswordHasher.php

namespace App\Tests\Unit\Identity\Application\TestDouble;

use App\Identity\Application\Port\PasswordHasherPort;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\PlainPassword;

final class FakePasswordHasher implements PasswordHasherPort
{
    private int $hashCount = 0;

    private int $verifyCount = 0;

    public function hash(PlainPassword $password): HashedPassword
    {
        ++$this->hashCount;

        return HashedPassword::fromRawHash('hashed:' . $password->toString());
    }

    public function verify(PlainPassword $password, HashedPassword $hash): bool
    {
        ++$this->verifyCount;

        return $hash->toString() === 'hashed:' . $password->toString();
    }

    public function hashCount(): int
    {
        return $this->hashCount;
    }

    public function verifyCount(): int
    {
        return $this->verifyCount;
    }
}
