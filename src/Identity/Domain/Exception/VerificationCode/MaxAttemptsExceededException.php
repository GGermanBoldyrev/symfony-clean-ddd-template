<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class MaxAttemptsExceededException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.max_attempts_exceeded';

    public static function exceeded(): self
    {
        return new self('Maximum number of verification attempts has been exceeded.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
