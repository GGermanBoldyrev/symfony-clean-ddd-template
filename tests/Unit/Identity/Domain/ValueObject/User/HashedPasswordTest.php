<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/ValueObject/User/HashedPasswordTest.php

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    #[Test]
    #[DataProvider('bcryptHashProvider')]
    public function itAcceptsBcryptHashes(string $hash): void
    {
        $password = HashedPassword::fromHash($hash);

        self::assertSame($hash, $password->toString());
    }

    #[Test]
    public function itRejectsNonBcryptHash(): void
    {
        $this->expectException(InvalidPasswordException::class);

        HashedPassword::fromHash('$argon2id$v=19$m=65536,t=4,p=1$hash');
    }

    #[Test]
    public function itCanBeReconstructedFromRawHash(): void
    {
        $password = HashedPassword::fromRawHash('legacy-hash');

        self::assertSame('legacy-hash', $password->toString());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function bcryptHashProvider(): array
    {
        return [
            '2y' => ['$2y$10$012345678901234567890123456789012345678901234567890123'],
            '2b' => ['$2b$10$012345678901234567890123456789012345678901234567890123'],
            '2a' => ['$2a$10$012345678901234567890123456789012345678901234567890123'],
        ];
    }
}
