<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Security/SecurityUserTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Security;

use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Infrastructure\Security\SecurityUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SecurityUserTest extends TestCase
{
    #[Test]
    public function itExposesSymfonySecurityUserContract(): void
    {
        $userId = UserId::generate();
        $securityUser = new SecurityUser($userId, HashedPassword::fromRawHash('hashed-password'));

        self::assertSame($userId->toString(), $securityUser->getUserIdentifier());
        self::assertSame('hashed-password', $securityUser->getPassword());
        self::assertSame(['ROLE_USER'], $securityUser->getRoles());

        $securityUser->eraseCredentials();
        self::assertSame('hashed-password', $securityUser->getPassword());
    }
}
