<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidUserIdException;
use App\Identity\Domain\ValueObject\User\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    #[Test]
    public function itGeneratesValidUuid(): void
    {
        $id = UserId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id->toString(),
        );
    }

    #[Test]
    public function itGeneratesUniqueIds(): void
    {
        $id1 = UserId::generate();
        $id2 = UserId::generate();

        self::assertFalse($id1->equals($id2));
    }

    #[Test]
    public function itAcceptsValidUuidString(): void
    {
        $uuid = '018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b';
        $id = UserId::fromString($uuid);

        self::assertSame($uuid, $id->toString());
    }

    #[Test]
    public function itRejectsInvalidUuidString(): void
    {
        $this->expectException(InvalidUserIdException::class);

        UserId::fromString('not-a-valid-uuid');
    }

    #[Test]
    public function itImplementsStringable(): void
    {
        $uuid = '018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b';
        $id = UserId::fromString($uuid);

        self::assertSame($uuid, (string) $id);
    }

    #[Test]
    public function itComparesEqualIds(): void
    {
        $uuid = '018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b';
        $a = UserId::fromString($uuid);
        $b = UserId::fromString($uuid);

        self::assertTrue($a->equals($b));
    }
}
