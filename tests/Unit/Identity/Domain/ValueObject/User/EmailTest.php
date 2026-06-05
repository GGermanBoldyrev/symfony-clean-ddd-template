<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\InvalidEmailException;
use App\Identity\Domain\ValueObject\User\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    #[DataProvider('validEmailProvider')]
    public function itAcceptsValidEmails(string $email): void
    {
        $vo = Email::fromString($email);

        self::assertInstanceOf(Email::class, $vo);
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function itRejectsInvalidEmails(string $email): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString($email);
    }

    #[Test]
    public function itNormalizesEmailToLowercase(): void
    {
        $email = Email::fromString('USER@EXAMPLE.COM');

        self::assertSame('user@example.com', $email->toString());
    }

    #[Test]
    public function itTrimsWhitespace(): void
    {
        $email = Email::fromString('  user@example.com  ');

        self::assertSame('user@example.com', $email->toString());
    }

    #[Test]
    public function itRejectsTooLongEmail(): void
    {
        // 321 characters total
        $longLocal = str_repeat('a', 310);
        $this->expectException(InvalidEmailException::class);

        Email::fromString($longLocal . '@example.com');
    }

    #[Test]
    public function itImplementsStringable(): void
    {
        $email = Email::fromString('user@example.com');

        self::assertSame('user@example.com', (string) $email);
    }

    #[Test]
    public function itComparesEqualEmails(): void
    {
        $a = Email::fromString('user@example.com');
        $b = Email::fromString('USER@EXAMPLE.COM');

        self::assertTrue($a->equals($b));
    }

    /** @return array<string, array{string}> */
    public static function validEmailProvider(): array
    {
        return [
            'simple' => ['user@example.com'],
            'subdomain' => ['user@mail.example.com'],
            'plus-addressing' => ['user+tag@example.com'],
            'numeric' => ['user123@example.org'],
            'hyphen domain' => ['user@my-domain.io'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function invalidEmailProvider(): array
    {
        return [
            'no-at' => ['notanemail'],
            'no-domain' => ['user@'],
            'no-local' => ['@example.com'],
            'spaces' => ['user @example.com'],
            'double-at' => ['user@@example.com'],
        ];
    }
}
