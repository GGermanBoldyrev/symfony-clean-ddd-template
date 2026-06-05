<?php

declare(strict_types=1);

// tests/Unit/Identity/Domain/Exception/DomainExceptionErrorCodeTest.php

namespace App\Tests\Unit\Identity\Domain\Exception;

use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;
use App\Identity\Domain\Exception\User\InvalidEmailException;
use App\Identity\Domain\Exception\User\InvalidPasswordException;
use App\Identity\Domain\Exception\User\InvalidUserIdException;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\Exception\VerificationCode\CodeExpiredException;
use App\Identity\Domain\Exception\VerificationCode\InvalidAttemptCountException;
use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\InvalidExpiresAtException;
use App\Identity\Domain\Exception\VerificationCode\InvalidMaxAttemptsException;
use App\Identity\Domain\Exception\VerificationCode\InvalidResendAfterException;
use App\Identity\Domain\Exception\VerificationCode\InvalidVerificationCodeIdException;
use App\Identity\Domain\Exception\VerificationCode\InvalidVerificationCodeValueException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
use App\Identity\Domain\ValueObject\User\Email;
use App\Shared\Domain\Exception\DomainExceptionInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DomainExceptionErrorCodeTest extends TestCase
{
    #[Test]
    #[DataProvider('exceptionProvider')]
    public function itExposesStableErrorCode(DomainExceptionInterface $exception, string $errorCode): void
    {
        self::assertSame($errorCode, $exception->getErrorCode());
        self::assertNotSame('', $exception->getMessage());
    }

    /**
     * @return array<string, array{DomainExceptionInterface, string}>
     */
    public static function exceptionProvider(): array
    {
        return [
            'data policy not accepted' => [
                DataPolicyNotAcceptedException::notAccepted(),
                'user.data_policy_not_accepted',
            ],
            'invalid email format' => [
                InvalidEmailException::invalidFormat('bad'),
                'user.invalid_email',
            ],
            'email too long' => [
                InvalidEmailException::tooLong(str_repeat('a', 321)),
                'user.invalid_email',
            ],
            'password too short' => [
                InvalidPasswordException::tooShort(8),
                'user.invalid_password',
            ],
            'password too long' => [
                InvalidPasswordException::tooLong(72),
                'user.invalid_password',
            ],
            'invalid password hash' => [
                InvalidPasswordException::invalidHash(),
                'user.invalid_password',
            ],
            'invalid user id' => [
                InvalidUserIdException::invalidFormat('bad'),
                'user.invalid_id',
            ],
            'user already verified' => [
                new UserAlreadyVerifiedException(Email::fromString('user@example.com')),
                'user.already_verified',
            ],
            'user not found by id' => [
                UserNotFoundException::withId('id'),
                'user.not_found',
            ],
            'user not found by email' => [
                UserNotFoundException::withEmail('user@example.com'),
                'user.not_found',
            ],
            'code expired' => [
                CodeExpiredException::expired(),
                'verification_code.expired',
            ],
            'invalid attempts' => [
                InvalidAttemptCountException::negative(-1),
                'verification_code.invalid_attempt_count',
            ],
            'invalid code' => [
                InvalidCodeException::mismatch(),
                'verification_code.invalid',
            ],
            'invalid expiration' => [
                InvalidExpiresAtException::notInFuture(),
                'verification_code.invalid_expires_at',
            ],
            'invalid max attempts' => [
                InvalidMaxAttemptsException::notPositive(0),
                'verification_code.invalid_max_attempts',
            ],
            'invalid resend past' => [
                InvalidResendAfterException::notInFuture(),
                'verification_code.invalid_resend_after',
            ],
            'invalid resend after expiration' => [
                InvalidResendAfterException::afterExpiration(),
                'verification_code.invalid_resend_after',
            ],
            'invalid verification code id' => [
                InvalidVerificationCodeIdException::invalidFormat('bad'),
                'verification_code.invalid_id',
            ],
            'invalid verification code value' => [
                InvalidVerificationCodeValueException::invalidFormat('abc'),
                'verification_code.invalid_value',
            ],
            'max attempts exceeded' => [
                MaxAttemptsExceededException::exceeded(),
                'verification_code.max_attempts_exceeded',
            ],
            'resend cooldown' => [
                ResendCooldownException::before(new DateTimeImmutable('+1 minute')),
                'verification_code.resend_cooldown',
            ],
            'verification code not found' => [
                VerificationCodeNotFoundException::forEmail('user@example.com'),
                'verification_code.not_found',
            ],
        ];
    }
}
