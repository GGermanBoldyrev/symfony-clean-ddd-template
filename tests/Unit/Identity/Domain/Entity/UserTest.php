<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Entity;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function itRegistersANewUser(): void
    {
        $user = $this->makeUser();

        self::assertFalse($user->isVerified());
        self::assertNull($user->verifiedAt);
    }

    #[Test]
    public function itVerifiesAnUnverifiedUser(): void
    {
        $user = $this->makeUser();

        $user->verify();

        self::assertTrue($user->isVerified());
        self::assertNotNull($user->verifiedAt);
    }

    #[Test]
    public function itUpdatesTimestampOnVerification(): void
    {
        $user = $this->makeUser();
        $before = $user->updatedAt;

        // Tiny sleep to ensure time advances
        usleep(1000);
        $user->verify();

        self::assertGreaterThanOrEqual($before, $user->updatedAt);
    }

    #[Test]
    public function itThrowsWhenVerifyingAlreadyVerifiedUser(): void
    {
        $user = $this->makeUser();
        $user->verify();

        $this->expectException(UserAlreadyVerifiedException::class);

        $user->verify();
    }

    #[Test]
    public function itPreservesImmutableFieldsAfterConstruction(): void
    {
        $id = UserId::generate();
        $email = Email::fromString('test@example.com');
        $passwordHash = HashedPassword::fromRawHash('$2y$10$somehashvalue');
        $dataPolicyAcceptedAt = DataPolicyAcceptedAt::accept(true);

        $user = User::register(
            id: $id,
            email: $email,
            passwordHash: $passwordHash,
            dataPolicyAcceptedAt: $dataPolicyAcceptedAt,
        );

        self::assertTrue($user->id->equals($id));
        self::assertTrue($user->email->equals($email));
        self::assertTrue($user->passwordHash->equals($passwordHash));
    }

    #[Test]
    public function itCanBeConstructedWithVerifiedAt(): void
    {
        $verifiedAt = new DateTimeImmutable();

        $user = new User(
            id: UserId::generate(),
            email: Email::fromString('verified@example.com'),
            passwordHash: HashedPassword::fromRawHash('$2y$10$somehash'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::accept(true),
            createdAt: new DateTimeImmutable(),
            verifiedAt: \App\Identity\Domain\ValueObject\User\VerifiedAt::fromDateTimeImmutable($verifiedAt),
        );

        self::assertTrue($user->isVerified());
    }

    private function makeUser(): User
    {
        return User::register(
            id: UserId::generate(),
            email: Email::fromString('user@example.com'),
            passwordHash: HashedPassword::fromRawHash('$2y$10$somehashvalue'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::accept(true),
        );
    }
}
