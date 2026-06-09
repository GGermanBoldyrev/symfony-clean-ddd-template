<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Application\Command\PurgeExpiredVerificationCodes;

use App\Identity\Application\Command\PurgeExpiredVerificationCodes\PurgeExpiredVerificationCodesCommand;
use App\Identity\Application\Command\PurgeExpiredVerificationCodes\PurgeExpiredVerificationCodesCommandHandler;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryVerificationCodeRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PurgeExpiredVerificationCodesCommandHandlerTest extends TestCase
{
    #[Test]
    public function itReturnsZeroWhenNoCodesExist(): void
    {
        $repo = new InMemoryVerificationCodeRepository();

        $result = $this->handler($repo)(new PurgeExpiredVerificationCodesCommand());

        self::assertSame(0, $result->deletedCount);
    }

    #[Test]
    public function itReturnsZeroWhenAllCodesAreStillValid(): void
    {
        $repo = new InMemoryVerificationCodeRepository();
        $repo->upsert($this->code('a@example.com', expiresAt: new DateTimeImmutable('+10 minutes')));
        $repo->upsert($this->code('b@example.com', expiresAt: new DateTimeImmutable('+5 minutes')));

        $result = $this->handler($repo)(new PurgeExpiredVerificationCodesCommand());

        self::assertSame(0, $result->deletedCount);
        self::assertSame(2, $repo->count());
    }

    #[Test]
    public function itDeletesOnlyExpiredCodes(): void
    {
        $repo = new InMemoryVerificationCodeRepository();
        $repo->upsert($this->code('expired1@example.com', expiresAt: new DateTimeImmutable('-5 minutes')));
        $repo->upsert($this->code('expired2@example.com', expiresAt: new DateTimeImmutable('-1 second')));
        $repo->upsert($this->code('valid@example.com', expiresAt: new DateTimeImmutable('+10 minutes')));

        $result = $this->handler($repo)(new PurgeExpiredVerificationCodesCommand());

        self::assertSame(2, $result->deletedCount);
        self::assertSame(1, $repo->count());
        self::assertNotNull($repo->findByEmail(Email::fromString('valid@example.com')));
        self::assertNull($repo->findByEmail(Email::fromString('expired1@example.com')));
        self::assertNull($repo->findByEmail(Email::fromString('expired2@example.com')));
    }

    #[Test]
    public function itDeletesAllCodesWhenAllAreExpired(): void
    {
        $repo = new InMemoryVerificationCodeRepository();
        $repo->upsert($this->code('a@example.com', expiresAt: new DateTimeImmutable('-1 hour')));
        $repo->upsert($this->code('b@example.com', expiresAt: new DateTimeImmutable('-1 minute')));

        $result = $this->handler($repo)(new PurgeExpiredVerificationCodesCommand());

        self::assertSame(2, $result->deletedCount);
        self::assertSame(0, $repo->count());
    }

    private function handler(InMemoryVerificationCodeRepository $repo): PurgeExpiredVerificationCodesCommandHandler
    {
        return new PurgeExpiredVerificationCodesCommandHandler($repo);
    }

    private function code(string $email, DateTimeImmutable $expiresAt): VerificationCode
    {
        return new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString($email),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable($expiresAt),
            resendAfter: ResendAfter::fromDateTimeImmutable(new DateTimeImmutable('+5 minutes')),
            createdAt: new DateTimeImmutable(),
        );
    }
}
