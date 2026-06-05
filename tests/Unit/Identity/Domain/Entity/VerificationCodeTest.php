<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Entity;

use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Exception\VerificationCode\CodeExpiredException;
use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Service\VerificationCodePolicy;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationCodeTest extends TestCase
{
    #[Test]
    public function itVerifiesCorrectCode(): void
    {
        $code = $this->makeValidCode('123456');

        $code->verify(VerificationCodeValue::fromString('123456'));

        self::assertSame(0, $code->attempts->toInt());
        self::assertGreaterThanOrEqual($code->createdAt, $code->updatedAt);
    }

    #[Test]
    public function itThrowsOnWrongCode(): void
    {
        $code = $this->makeValidCode('123456');

        $this->expectException(InvalidCodeException::class);

        $code->verify(VerificationCodeValue::fromString('999999'));
    }

    #[Test]
    public function itIncrementsAttemptsOnWrongCode(): void
    {
        $code = $this->makeValidCode('123456');

        try {
            $code->verify(VerificationCodeValue::fromString('000000'));
        } catch (InvalidCodeException) {
        }

        self::assertSame(1, $code->attempts->toInt());
    }

    #[Test]
    public function itThrowsMaxAttemptsExceededAfterTooManyWrongCodes(): void
    {
        $code = $this->makeValidCode('123456', maxAttempts: 3);

        for ($i = 0; $i < 2; ++$i) {
            try {
                $code->verify(VerificationCodeValue::fromString('000000'));
            } catch (InvalidCodeException) {
            }
        }

        $this->expectException(MaxAttemptsExceededException::class);

        $code->verify(VerificationCodeValue::fromString('000000'));
    }

    #[Test]
    public function itThrowsMaxAttemptsExceededImmediatelyWhenAlreadyExhausted(): void
    {
        $code = $this->makeValidCode('123456', maxAttempts: 1);

        try {
            $code->verify(VerificationCodeValue::fromString('000000'));
        } catch (MaxAttemptsExceededException) {
        }

        $this->expectException(MaxAttemptsExceededException::class);

        $code->verify(VerificationCodeValue::fromString('123456'));
    }

    #[Test]
    public function itThrowsCodeExpiredWhenExpired(): void
    {
        $now = new DateTimeImmutable();
        $expiresAt = ExpiresAt::fromDateTimeImmutable($now->modify('-1 second'));

        $code = new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: $expiresAt,
            resendAfter: ResendAfter::fromDateTimeImmutable($now->modify('-1 second')),
            createdAt: $now->modify('-2 minutes'),
        );

        $this->expectException(CodeExpiredException::class);

        $code->verify(VerificationCodeValue::fromString('123456'));
    }

    #[Test]
    public function itReportsExpiredStatus(): void
    {
        $now = new DateTimeImmutable();
        $code = new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable($now->modify('-1 second')),
            resendAfter: ResendAfter::fromDateTimeImmutable($now->modify('-1 second')),
            createdAt: $now->modify('-2 minutes'),
        );

        self::assertTrue($code->isExpired());
    }

    #[Test]
    public function itReportsExhaustedStatus(): void
    {
        $now = new DateTimeImmutable();
        $code = new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::fromInt(5),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable($now->modify('+15 minutes')),
            resendAfter: ResendAfter::fromDateTimeImmutable($now->modify('+60 seconds')),
            createdAt: $now,
        );

        self::assertTrue($code->isExhausted());
    }

    #[Test]
    public function itAllowsResendAfterCooldownPasses(): void
    {
        $now = new DateTimeImmutable();
        $code = new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable($now->modify('+15 minutes')),
            resendAfter: ResendAfter::fromDateTimeImmutable($now->modify('-1 second')),
            createdAt: $now->modify('-2 minutes'),
        );

        $code->assertCanResend();

        self::assertFalse($code->isExhausted());
    }

    #[Test]
    public function itDisallowsResendBeforeCooldownPasses(): void
    {
        $now = new DateTimeImmutable();
        $code = new VerificationCode(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            attempts: AttemptCount::zero(),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: ExpiresAt::fromDateTimeImmutable($now->modify('+15 minutes')),
            resendAfter: ResendAfter::fromDateTimeImmutable($now->modify('+30 seconds')),
            createdAt: $now,
        );

        $this->expectException(ResendCooldownException::class);

        $code->assertCanResend();
    }

    #[Test]
    public function itStartsWithZeroAttempts(): void
    {
        $now = new DateTimeImmutable();
        $code = VerificationCode::issue(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString('123456'),
            maxAttempts: MaxAttempts::fromInt(5),
            expiresAt: VerificationCodePolicy::expiresAt($now),
            resendAfter: VerificationCodePolicy::resendAfter($now, VerificationCodePolicy::expiresAt($now)),
        );

        self::assertSame(0, $code->attempts->toInt());
    }

    private function makeValidCode(string $value, int $maxAttempts = 5): VerificationCode
    {
        $now = new DateTimeImmutable();

        return VerificationCode::issue(
            id: VerificationCodeId::generate(),
            email: Email::fromString('test@example.com'),
            code: VerificationCodeValue::fromString($value),
            maxAttempts: MaxAttempts::fromInt($maxAttempts),
            expiresAt: VerificationCodePolicy::expiresAt($now),
            resendAfter: VerificationCodePolicy::resendAfter($now, VerificationCodePolicy::expiresAt($now)),
        );
    }
}
