<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Query/GetCurrentUser/GetCurrentUserQueryHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Query\GetCurrentUser;

use App\Identity\Application\Query\GetCurrentUser\GetCurrentUserQuery;
use App\Identity\Application\Query\GetCurrentUser\GetCurrentUserQueryHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCurrentUserQueryHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
    }

    #[Test]
    public function itReturnsCurrentUnverifiedUser(): void
    {
        $user = $this->user(verified: false);
        $this->users()->save($user);

        $dto = $this->handler()(new GetCurrentUserQuery($user->id->toString()));

        self::assertSame($user->id->toString(), $dto->id);
        self::assertSame('user@example.com', $dto->email);
        self::assertFalse($dto->isVerified);
        self::assertNull($dto->verifiedAt);
        self::assertSame('2024-01-01T12:00:00+00:00', $dto->dataPolicyAcceptedAt);
    }

    #[Test]
    public function itReturnsCurrentVerifiedUser(): void
    {
        $user = $this->user(verified: true);
        $this->users()->save($user);

        $dto = $this->handler()(new GetCurrentUserQuery($user->id->toString()));

        self::assertTrue($dto->isVerified);
        self::assertSame('2024-01-01T12:05:00+00:00', $dto->verifiedAt);
    }

    #[Test]
    public function itRejectsMissingUser(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->handler()(new GetCurrentUserQuery(UserId::generate()->toString()));
    }

    private function handler(): GetCurrentUserQueryHandler
    {
        return new GetCurrentUserQueryHandler($this->users());
    }

    private function users(): InMemoryUserRepository
    {
        return $this->users ?? throw new LogicException('User repository is not initialized.');
    }

    private function user(bool $verified): User
    {
        return new User(
            id: UserId::generate(),
            email: Email::fromString('user@example.com'),
            passwordHash: HashedPassword::fromRawHash('hashed:secret123'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01T12:00:00+00:00')),
            createdAt: new DateTimeImmutable('2024-01-01T12:00:00+00:00'),
            verifiedAt: $verified ? VerifiedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01T12:05:00+00:00')) : null,
        );
    }
}
