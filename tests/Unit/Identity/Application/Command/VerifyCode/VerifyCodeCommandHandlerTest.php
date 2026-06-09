<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Command/VerifyCode/VerifyCodeCommandHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Command\VerifyCode;

use App\Identity\Application\Command\VerifyCode\VerifyCodeCommand;
use App\Identity\Application\Command\VerifyCode\VerifyCodeCommandHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Exception\VerificationCode\CodeExpiredException;
use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
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
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryVerificationCodeRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerifyCodeCommandHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    private ?InMemoryVerificationCodeRepository $codes = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->codes = new InMemoryVerificationCodeRepository();
    }

    #[Test]
    public function itVerifiesUserAndDeletesCode(): void
    {
        $email = Email::fromString('user@example.com');
        $this->users()->save($this->user($email, verified: false));
        $this->codes()->upsert($this->verificationCode($email, value: '123456'));

        $this->handler()(new VerifyCodeCommand('user@example.com', '123456'));

        $user = $this->users()->findByEmail($email);
        self::assertNotNull($user);
        self::assertTrue($user->isVerified());
        self::assertSame(0, $this->codes()->count());
        self::assertSame(1, $this->codes()->deleteCount());
        self::assertSame(2, $this->users()->saveCount());
    }

    #[Test]
    public function itDeletesCodeWhenUserDoesNotExist(): void
    {
        $email = Email::fromString('user@example.com');
        $this->codes()->upsert($this->verificationCode($email, value: '123456'));

        $this->handler()(new VerifyCodeCommand('user@example.com', '123456'));

        self::assertSame(0, $this->codes()->count());
        self::assertSame(1, $this->codes()->deleteCount());
        self::assertSame(0, $this->users()->count());
    }

    #[Test]
    public function itRejectsMissingVerificationCode(): void
    {
        $this->expectException(VerificationCodeNotFoundException::class);

        $this->handler()(new VerifyCodeCommand('user@example.com', '123456'));
    }

    #[Test]
    public function itSavesAttemptCountWhenSubmittedCodeIsWrong(): void
    {
        $email = Email::fromString('user@example.com');
        $this->users()->save($this->user($email, verified: false));
        $this->codes()->upsert($this->verificationCode($email, value: '123456'));

        try {
            $this->handler()(new VerifyCodeCommand('user@example.com', '000000'));
            self::fail('Expected invalid code exception.');
        } catch (InvalidCodeException) {
            $code = $this->codes()->findByEmail($email);
            self::assertNotNull($code);
            self::assertSame(1, $code->attempts->toInt());
            self::assertSame(1, $this->codes()->saveCount());
            self::assertSame(0, $this->codes()->deleteCount());
        }
    }

    #[Test]
    public function itSavesAttemptCountWhenWrongCodeExhaustsAttempts(): void
    {
        $email = Email::fromString('user@example.com');
        $this->codes()->upsert($this->verificationCode($email, value: '123456', maxAttempts: 1));

        try {
            $this->handler()(new VerifyCodeCommand('user@example.com', '000000'));
            self::fail('Expected max attempts exception.');
        } catch (MaxAttemptsExceededException) {
            $code = $this->codes()->findByEmail($email);
            self::assertNotNull($code);
            self::assertSame(1, $code->attempts->toInt());
            self::assertSame(1, $this->codes()->saveCount());
        }
    }

    #[Test]
    public function itRejectsExpiredCodeWithoutPersistingAttempts(): void
    {
        $email = Email::fromString('user@example.com');
        $this->codes()->upsert($this->verificationCode($email, value: '123456', expired: true));

        try {
            $this->handler()(new VerifyCodeCommand('user@example.com', '123456'));
            self::fail('Expected expired code exception.');
        } catch (CodeExpiredException) {
            self::assertSame(0, $this->codes()->saveCount());
            self::assertSame(0, $this->codes()->deleteCount());
        }
    }

    private function handler(): VerifyCodeCommandHandler
    {
        return new VerifyCodeCommandHandler($this->users(), $this->codes());
    }

    private function users(): InMemoryUserRepository
    {
        return $this->users ?? throw new LogicException('User repository is not initialized.');
    }

    private function codes(): InMemoryVerificationCodeRepository
    {
        return $this->codes ?? throw new LogicException('Verification code repository is not initialized.');
    }

    private function user(Email $email, bool $verified): User
    {
        return new User(
            id: UserId::generate(),
            email: $email,
            passwordHash: HashedPassword::fromRawHash('hashed:secret123'),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:00:00')),
            createdAt: new DateTimeImmutable('2024-01-01 12:00:00'),
            verifiedAt: $verified ? VerifiedAt::fromDateTimeImmutable(new DateTimeImmutable('2024-01-01 12:05:00')) : null,
        );
    }

    private function verificationCode(
        Email $email,
        string $value,
        int $maxAttempts = 5,
        bool $expired = false,
    ): VerificationCode {
        return new VerificationCode(
            id: VerificationCodeId::generate(),
            email: $email,
            code: VerificationCodeValue::fromString($value),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt($maxAttempts),
            expiresAt: ExpiresAt::fromDateTimeImmutable(new DateTimeImmutable($expired ? '-1 minute' : '+10 minutes')),
            resendAfter: ResendAfter::fromDateTimeImmutable(new DateTimeImmutable('-1 minute')),
            createdAt: new DateTimeImmutable('-2 minutes'),
        );
    }
}
