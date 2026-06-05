<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlainPasswordTest extends TestCase
{
    #[Test]
    public function itAcceptsPasswordOfMinimumLength(): void
    {
        $password = PlainPassword::fromString('12345678');

        self::assertSame('12345678', $password->toString());
    }

    #[Test]
    public function itAcceptsPasswordOfMaximumLength(): void
    {
        $value = str_repeat('a', 72);
        $password = PlainPassword::fromString($value);

        self::assertSame($value, $password->toString());
    }

    #[Test]
    public function itRejectsTooShortPassword(): void
    {
        $this->expectException(InvalidPasswordException::class);

        PlainPassword::fromString('1234567');
    }

    #[Test]
    public function itRejectsTooLongPassword(): void
    {
        $this->expectException(InvalidPasswordException::class);

        PlainPassword::fromString(str_repeat('a', 73));
    }

    #[Test]
    public function itAcceptsPasswordWithSpecialCharacters(): void
    {
        $password = PlainPassword::fromString('P@ssw0rd!#$%');

        self::assertSame('P@ssw0rd!#$%', $password->toString());
    }
}
