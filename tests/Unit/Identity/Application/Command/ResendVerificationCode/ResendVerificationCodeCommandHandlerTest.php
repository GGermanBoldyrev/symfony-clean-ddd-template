<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Command/ResendVerificationCode/ResendVerificationCodeCommandHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Command\ResendVerificationCode;

use App\Identity\Application\Command\ResendVerificationCode\ResendVerificationCodeCommand;
use App\Identity\Application\Command\ResendVerificationCode\ResendVerificationCodeCommandHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use App\Tests\Unit\Identity\Application\TestDouble\FixedVerificationCodeGenerator;
use App\Tests\Unit\Identity\Application\TestDouble\SpyVerificationMailer;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryVerificationCodeRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResendVerificationCodeCommandHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    private ?InMemoryVerificationCodeRepository $codes = null;

    private ?FixedVerificationCodeGenerator $generator = null;

    private ?SpyVerificationMailer $mailer = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->codes = new InMemoryVerificationCodeRepository();
        $this->generator = new FixedVerificationCodeGenerator(['654321']);
        $this->mailer = new SpyVerificationMailer();
    }

    #[Test]
    public function itSilentlyIgnoresUnknownUser(): void
    {
        $this->handler()(new ResendVerificationCodeCommand('missing@example.com'));

        self::assertSame(0, $this->codes()->count());
        self::assertSame(0, $this->mailer()->count());
    }

    #[Test]
    public function itSilentlySkipsSendingWhenCooldownIsActive(): void
    {
        $email = Email::fromString('user@example.com');
        $this->users()->save($this->user('user@example.com', verified: false));
        $this->codes()->upsert($this->verificationCode($email, resendAfter: new DateTimeImmutable('+1 minute')));

        $this->handler()(new ResendVerificationCodeCommand('user@example.com'));

        self::assertSame(1, $this->codes()->count());
        self::assertSame(1, $this->codes()->upsertCount());
        self::assertSame(0, $this->mailer()->count());
    }

    #[Test]
    public function itSilentlyIgnoresVerifiedUser(): void
    {
        $this->users()->save($this->user('user@example.com', verified: true));

        $this->handler()(new ResendVerificationCodeCommand('user@example.com'));

        self::assertSame(0, $this->codes()->count());
        self::assertSame(0, $this->mailer()->count());
    }

    #[Test]
    public function itSendsNewCodeForUnverifiedUser(): void
    {
        $this->users()->save($this->user('user@example.com', verified: false));

        $this->handler()(new ResendVerificationCodeCommand('user@example.com'));

        self::assertSame(1, $this->codes()->count());
        self::assertSame(1, $this->codes()->upsertCount());
        self::assertSame([['email' => 'user@example.com', 'code' => '654321']], $this->mailer()->sent());
    }

    #[Test]
    public function itReplacesExistingCodeAfterCooldown(): void
    {
        $email = Email::fromString('user@example.com');
        $this->users()->save($this->user('user@example.com', verified: false));
        $this->codes()->upsert($this->verificationCode($email, resendAfter: new DateTimeImmutable('-1 minute')));

        $this->handler()(new ResendVerificationCodeCommand('user@example.com'));

        $code = $this->codes()->findByEmail($email);
        self::assertNotNull($code);
        self::assertSame('654321', $code->code->toString());
        self::assertSame(1, $this->codes()->count());
        self::assertSame(2, $this->codes()->upsertCount());
        self::assertSame([['email' => 'user@example.com', 'code' => '654321']], $this->mailer()->sent());
    }

    private function handler(): ResendVerificationCodeCommandHandler
    {
        return new ResendVerificationCodeCommandHandler(
            $this->users(),
            $this->codes(),
            $this->generator(),
            $this->mailer(),
        );
    }

    private function users(): InMemoryUserRepository
    {
        return $this->users ?? throw new LogicException('User repository is not initialized.');
    }

    private function codes(): InMemoryVerificationCodeRepository
    {
        return $this->codes ?? throw new LogicException('Verification code repository is not initialized.');
    }

    private function generator(): FixedVerificationCodeGenerator
    {
        return $this->generator ?? throw new LogicException('Verification code generator is not initialized.');
    }

    private function mailer(): SpyVerificationMailer
    {
        return $this->mailer ?? throw new LogicException('Verification mailer is not initialized.');
    }

    private function user(string $email, bool $verified): User
    {
        return new User(
            id: UserId::generate(),
            email: Email::fromString($email),
            passwordHash: HashedPassword::fromRawHash('hashed:secret123'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:00:00')),
            createdAt: new DateTimeImmutable('2024-01-01 12:00:00'),
            verifiedAt: $verified ? VerifiedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:05:00')) : null,
        );
    }

    private function verificationCode(Email $email, DateTimeImmutable $resendAfter): VerificationCode
    {
        return new VerificationCode(
            id: VerificationCodeId::generate(),
            email: $email,
            code: VerificationCodeValue::fromString('000000'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable(new DateTimeImmutable('+10 minutes')),
            resendAfter: ResendAfter::fromDateTimeImmutable($resendAfter),
            createdAt: new DateTimeImmutable('-2 minutes'),
        );
    }
}
