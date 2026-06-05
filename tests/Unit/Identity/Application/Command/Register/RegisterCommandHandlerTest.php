<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/Command/Register/RegisterCommandHandlerTest.php

namespace App\Tests\Unit\Identity\Application\Command\Register;

use App\Identity\Application\Command\Register\RegisterCommand;
use App\Identity\Application\Command\Register\RegisterCommandHandler;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;
use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
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
use App\Tests\Unit\Identity\Application\TestDouble\FakePasswordHasher;
use App\Tests\Unit\Identity\Application\TestDouble\FixedVerificationCodeGenerator;
use App\Tests\Unit\Identity\Application\TestDouble\SpyVerificationMailer;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryUserRepository;
use App\Tests\Unit\Identity\Infrastructure\InMemory\InMemoryVerificationCodeRepository;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegisterCommandHandlerTest extends TestCase
{
    private ?InMemoryUserRepository $users = null;

    private ?InMemoryVerificationCodeRepository $codes = null;

    private ?FakePasswordHasher $hasher = null;

    private ?FixedVerificationCodeGenerator $codeGenerator = null;

    private ?SpyVerificationMailer $mailer = null;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->codes = new InMemoryVerificationCodeRepository();
        $this->hasher = new FakePasswordHasher();
        $this->codeGenerator = new FixedVerificationCodeGenerator(['123456']);
        $this->mailer = new SpyVerificationMailer();
    }

    #[Test]
    public function itRegistersNewUserAndSendsVerificationCode(): void
    {
        $this->handler()(new RegisterCommand(' USER@EXAMPLE.COM ', 'secret123', true));

        $user = $this->users()->findByEmail(Email::fromString('user@example.com'));
        $code = $this->codes()->findByEmail(Email::fromString('user@example.com'));

        self::assertSame(1, $this->users()->count());
        self::assertNotNull($user);
        self::assertFalse($user->isVerified());
        self::assertSame('hashed:secret123', $user->passwordHash->toString());
        self::assertSame(1, $this->hasher()->hashCount());
        self::assertNotNull($code);
        self::assertSame('123456', $code->code->toString());
        self::assertSame(1, $this->codes()->upsertCount());
        self::assertSame([['email' => 'user@example.com', 'code' => '123456']], $this->mailer()->sent());
    }

    #[Test]
    public function itResendsCodeForExistingUnverifiedUserWithoutRehashingPassword(): void
    {
        $this->users()->save($this->user('user@example.com', verified: false));

        $this->handler()(new RegisterCommand('user@example.com', 'different-password', true));

        self::assertSame(1, $this->users()->count());
        self::assertSame(0, $this->hasher()->hashCount());
        self::assertSame(1, $this->codes()->count());
        self::assertSame([['email' => 'user@example.com', 'code' => '123456']], $this->mailer()->sent());
    }

    #[Test]
    public function itRejectsAlreadyVerifiedUser(): void
    {
        $this->users()->save($this->user('user@example.com', verified: true));

        try {
            $this->handler()(new RegisterCommand('user@example.com', 'secret123', true));
            self::fail('Expected user already verified exception.');
        } catch (UserAlreadyVerifiedException) {
            self::assertSame(0, $this->codes()->count());
            self::assertSame(0, $this->mailer()->count());
        }
    }

    #[Test]
    public function itRejectsResendDuringCooldown(): void
    {
        $email = Email::fromString('user@example.com');
        $this->users()->save($this->user('user@example.com', verified: false));
        $this->codes()->upsert($this->verificationCode($email, resendAfter: new DateTimeImmutable('+1 minute')));

        try {
            $this->handler()(new RegisterCommand('user@example.com', 'secret123', true));
            self::fail('Expected resend cooldown exception.');
        } catch (ResendCooldownException) {
            self::assertSame(1, $this->codes()->count());
            self::assertSame(1, $this->codes()->upsertCount());
            self::assertSame(0, $this->mailer()->count());
        }
    }

    #[Test]
    public function itRejectsInvalidPasswordBeforeSavingUser(): void
    {
        try {
            $this->handler()(new RegisterCommand('user@example.com', 'short', true));
            self::fail('Expected invalid password exception.');
        } catch (InvalidPasswordException) {
            self::assertSame(0, $this->users()->count());
            self::assertSame(0, $this->codes()->count());
            self::assertSame(0, $this->mailer()->count());
        }
    }

    #[Test]
    public function itRejectsRegistrationWithoutDataPolicyAcceptance(): void
    {
        try {
            $this->handler()(new RegisterCommand('user@example.com', 'secret123', false));
            self::fail('Expected data policy exception.');
        } catch (DataPolicyNotAcceptedException) {
            self::assertSame(0, $this->users()->count());
            self::assertSame(0, $this->codes()->count());
            self::assertSame(1, $this->hasher()->hashCount());
        }
    }

    private function handler(): RegisterCommandHandler
    {
        return new RegisterCommandHandler(
            $this->users(),
            $this->codes(),
            $this->hasher(),
            $this->codeGenerator(),
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

    private function hasher(): FakePasswordHasher
    {
        return $this->hasher ?? throw new LogicException('Password hasher is not initialized.');
    }

    private function codeGenerator(): FixedVerificationCodeGenerator
    {
        return $this->codeGenerator ?? throw new LogicException('Verification code generator is not initialized.');
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
            createdAt: new DateTimeImmutable('-1 minute'),
        );
    }
}
